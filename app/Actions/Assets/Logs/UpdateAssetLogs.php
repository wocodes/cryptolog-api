<?php

namespace App\Actions\Assets\Logs;

use App\Actions\Binance\GetAssets24hTicker;
use App\Actions\User\Update;
use App\Models\Asset;
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

    private string $currentAsset;
    private int $countImport = 0;
    private ?Authenticatable $user;
    private $userApiKeys;
    private const API_URL = "https://api.binance.com/api/v3";

    protected static $commandSignature = 'import';

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
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->user = $this->user();
        $this->userApiKeys = $this->user->apiKeys()->first();

        $assets = Asset::all();

        foreach ($assets as $asset) {
            $url = $this->buildUrl($asset);

            try {
                $assets = Http::withHeaders(["X-MBX-APIKEY" => $this->userApiKeys->key])->get($url)->json();

                $this->logByOrders($assets);
            } catch (RequestException $requestException) {
                throw new \HttpRequestException($requestException->getMessage());
            } catch (\Throwable $throwable) {
                dd($throwable->getMessage());
            }
        }

        // update fetched_remote_balances_at column
        $this->user()->fetched_remote_orders_at = now();
        $this->user()->save();

        return $this->countImport;
    }

    /**
     * @throws \Exception
     */
    public function asCommand()
    {
        $this->handle();
    }


    private function logByOrders(array $assets)
    {
        foreach ($assets as $asset) {
            if ($asset['side'] == "BUY") {
                $this->purchase($asset);
            } elseif ($asset['side'] == "SELL") {
                $this->withdrawal($asset);
            } else {
                Log::info("Auto Log isn't a BUY or SELL");
            }
        }
    }

    private function purchase($asset)
    {
        $data = [
            "platform_id" => Platform::whereName("Binance")->firstOrFail()->id,
            "asset_id" => Asset::where('symbol', $this->currentAsset)->firstOrFail()->id,
            "quantity_bought" => $asset['origQty'], // OR $asset['executedQty'] (is one of them)
            "initial_value" => $asset['price'] * $asset['origQty'],
            "date_of_purchase" => Carbon::make($asset['time'])->toDate(),
        ];

        CreateLog::make($data);
    }


    private function withdrawal($asset)
    {
        $getWithrawableLog = $this->user->assetLogs()->whereHas('asset', function ($query) {
            $query->whereName($this->currentAsset);
        })->where('quantity_bought', '>', $asset['origQty'])
            ->orderBy('profit_loss', 'DESC')->first();

        $data = [
            'log_id' => $getWithrawableLog->id,
            'value' => $asset['price'] * $asset['origQty'],
            'quantity' => $asset['origQty'],
            'date' => Carbon::make($asset['time'])->toDate(),
        ];

        CreateWithdrawal::make($data);
    }


    private function buildUrl($asset)
    {
        $this->currentAsset = $asset->symbol;
        $symbolPairs = "{$this->currentAsset}USDT";
        $lastUpdate = $this->user()->fetched_remote_orders_at ?? $this->user()->fetched_remote_balance_at;
        $timestamp = Carbon::parse($lastUpdate)->getTimestampMs();

        $url = static::API_URL . "/all_orders";

        $queryString = "timestamp=$timestamp";
        $queryString .= "&startTime=$timestamp";
        $queryString .= "&symbol=$symbolPairs";
        $queryString .= "&limit=500";

        $signature = hash_hmac("sha256", $queryString, $this->userApiKeys->secret);
        $url .= "?$queryString&signature=$signature";

        return $url;
    }
}
