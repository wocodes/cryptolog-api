<?php

namespace App\Actions\Assets\Logs;

use App\Actions\Binance\GetAssets24hTicker;
use App\Actions\User\Update;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Platform;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Action;

class UpdateAssetLogs extends Action implements ShouldQueue
{
    use Queueable;

//    private int $binanceServerTimestamp;
    private string $currentAsset;
    private int $countImport = 0;
    private ?Authenticatable $user = null;
    private $userApiKeys;
    private const API_URL = "https://api2.binance.com/api/v3";

    protected static $commandSignature = 'update:logs {--user_id=}';

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "user_id" => "nullable|integer"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->user = $this->user();

        if (!$this->user && $this->user_id) {
            $this->user = User::findOrFail($this->user_id);
        }

        Log::info("Updating Existing Logs (sell/buy) for User {$this->user->id}");

        $user = $this->user;

        $this->userApiKeys = $this->user->apiKeys()->first();
        if (!$this->userApiKeys) {
//            Log::info("User has no api keys set. Can't get orders");
            return false;
        }

        $assets = Asset::whereHas("logs", function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get()->pluck('symbol')->toArray();

        Log::info("User Assets", [$assets]);

        foreach ($assets as $asset) {
            Log::info(" ");
            $url = $this->buildUrl($asset);
            Log::info("The url", [$url]);

            try {
                $orders = Http::withHeaders(["X-MBX-APIKEY" => $this->userApiKeys->key])->retry(3)->get($url)->json();
                Log::info("The asset", [$this->currentAsset]);
                Log::info("The response", [$orders]);
                // {"code":-1121,"msg":"Invalid symbol."}

                if (!empty($orders['code']) && $orders['code'] == -1121) {
                    Log::info($orders['msg']);
                } elseif (count($orders)) {
                    $this->logByOrders($orders);
                } else {
                    Log::info('No recent orders');
                }
            } catch (RequestException $requestException) {
                Log::error($requestException->response);
                Log::error($requestException->getCode());
                Log::error($requestException->getMessage());

//                throw new \Exception($requestException->getMessage());
            } catch (\Throwable $throwable) {
                Log::error($throwable->getMessage());
//                throw new \Exception($throwable->getMessage());
            }
        }

        // update fetched_remote_balances_at column
        $this->user->fetched_remote_orders_at = now();
        $this->user->save();

        return $this->countImport;
    }

    /**
     * @throws \Exception
     */
    public function asCommand()
    {

    }

    private function logByOrders(array $orders)
    {
        Log::info('the orders', $orders);
        foreach ($orders as $order) {
            Log::info('the order', $order);
            if ($order['side'] === "BUY") {
                $this->purchase($order);
            } elseif ($order['side'] === "SELL") {
                $this->withdrawal($order);
            } else {
                Log::info("Auto Log isn't a BUY or SELL");
            }
        }
    }

    private function purchase($asset)
    {
        Log::info("purchased asset", [$asset]);
        $initialValue = $asset['price'] * $asset['origQty'];

        $data = [
            "platform_id" => Platform::whereName("Binance")->firstOrFail()->id,
            "asset_id" => Asset::where('symbol', $this->currentAsset)->firstOrFail()->id,
            "quantity_bought" => $asset['origQty'], // OR $asset['executedQty'] (is one of them)
            "current_quantity" => $asset['origQty'], // OR $asset['executedQty'] (is one of them)
            "initial_value" => (string) $initialValue,
            "date_of_purchase" => Carbon::parse($asset['time'])->toDate(),
            "user_id" => $this->user->id,
        ];

        CreateLog::run($data);
    }


    private function withdrawal($asset)
    {
        Log::info("withdrawn asset", [$asset]);

        $getWithrawableLog = $this->user->assetLogs()->whereHas('asset', function ($query) {
            $query->whereName($this->currentAsset);
        })->where('current_quantity', '>', $asset['origQty'])
            ->orderBy('profit_loss', 'DESC')->first();

        $data = [
            'log_id' => $getWithrawableLog->id,
            'value' => $asset['price'] * $asset['origQty'],
            'quantity' => $asset['origQty'],
            'date' => Carbon::parse($asset['time'])->toDate(),
        ];

        CreateWithdrawal::run($data);
    }

//    private function getBinanceServerTimestamp()
//    {
//        try {
//            // get current server time
//            $response = Http::get(static::API_URL . "/time")->json();
//            $this->binanceServerTimestamp = $response['serverTime'];
//        } catch (RequestException $requestException) {
//            throw new \HttpRequestException($requestException->getMessage());
//        } catch (\Throwable $throwable) {
//            throw new \ErrorException($throwable->getMessage());
//        }
//    }

    private function buildUrl($asset)
    {
//        $this->getBinanceServerTimestamp();

        $timestamp = now()->getTimestampMs() + 1000;
//        $humanTime = Carbon::createFromTimestampMs($timestamp)->toDateTimeString();
//        $bHumanTime = Carbon::createFromTimestampMs($this->binanceServerTimestamp)->toDateTimeString();

//        Log::info("My Server Time: $timestamp: $humanTime");
//        Log::info("Binance Server Time: $this->binanceServerTimestamp: $bHumanTime");
//        Log::info("Last Update: {$this->user->fetched_remote_orders_at}");

        $this->currentAsset = $asset;
        $symbolPairs = "{$this->currentAsset}USDT";
        $lastUpdate = $this->user->fetched_remote_orders_at ?? $this->user->fetched_remote_balance_at;
        $lastUpdateTimestamp = Carbon::parse($lastUpdate)->getTimestampMs();

        $url = static::API_URL . "/allOrders";

        $queryString = "timestamp=$timestamp";
        $queryString .= "&startTime=$lastUpdateTimestamp";
        $queryString .= "&symbol=$symbolPairs";
        $queryString .= "&limit=500";

        $signature = hash_hmac("sha256", $queryString, $this->userApiKeys->secret);
        $url .= "?$queryString&signature=$signature";

        return $url;
    }
}
