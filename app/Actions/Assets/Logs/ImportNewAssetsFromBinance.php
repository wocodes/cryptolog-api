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

class ImportNewAssetsFromBinance extends Action implements ShouldQueue
{
    use Queueable;

    private int $binanceServerTimestamp;
    private string $currentAsset;
    private int $countImport = 0;
    private ?Authenticatable $user;
    private $userApiKeys;
    private const API_URL = "https://api.binance.com/api/v3";
    private const ENDPOINTS = [
      "all_orders" => [
          "path" => "/allOrders",
          "params" => [
              "symbol" => "required",
              "limit" => "optional",
          ]
      ],
      "account" => [
          "path" => "/account",
          "params" => []
      ]
    ];

    private $selectedEndpoint = self::ENDPOINTS['account'];

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
        Log::info("Importing Balance from Binance");
        $this->user = $this->user();
        $this->userApiKeys = $this->user->apiKeys()->first();

        try {
            $url = $this->buildUrl();
            Log::info("Fetching balances... $url");

            $assets = Http::withHeaders(["X-MBX-APIKEY" => $this->userApiKeys->key])->retry(3)->get($url)->json();

            $this->logByAccountBalance($assets['balances']);
            $this->countImport++;
        } catch (RequestException $requestException) {
            Log::error("An error occurred.", [$requestException->response]);
            Log::error("Code", [$requestException->response['code']]);
            Log::error("Message", [$requestException->response['msg']]);

            if ($requestException->response['code'] == -1021) {
                throw new \Exception("No new updates at this time. Try again later.", 400);
            } else {
                throw new \Exception("An error occurred");
//                throw new \Exception($requestException->response['msg']);
            }

        } catch (\Throwable $throwable) {
            dd($throwable->getMessage());
        }

        // update fetched_remote_balances_at column
        $this->user()->fetched_remote_balance_at = now();
        $this->user()->save();

        Log::info("Finished importing balance from Binance");
        return $this->countImport;
    }

    /**
     * NOTE: Observations!
     * 1. The initial plan was to get ALL the users past orders since they started using the platform, but Binance only allows fetching
     * data from the past 3 months which is a bummer! :-( Therefore an alternative approach is to get the user's wallet balance of each
     * crypto instead. Not cool enough though, but it's a good start anyway.
     *
     * @throws \Exception
     */
    public function asCommand()
    {
        $this->handle();
    }

//    /**
//     * @throws \HttpRequestException
//     * @throws \Exception
//     */
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

    private function logByOrders(array $assets)
    {
        foreach ($assets as $asset) {
            if ($this->selectedEndpoint === 'all_orders') {
                if ($asset['side'] == "BUY") {
                    $this->purchase($asset);
                } elseif ($asset['side'] == "SELL") {
                    $this->withdrawal($asset);
                } else {
                    Log::info("Auto Log isn't a BUY or SELL");
                }
            }
        }
    }

    private function logByAccountBalance(array $accountBalances)
    {
        foreach ($accountBalances as $accountBalance) {
            // check if asset is saved in Binance LenDing (i.e Savings)
            // if so, strip the LD prefix from the asset.
            if (substr($accountBalance['asset'], 0, 2) === 'LD') {
                $accountBalance['asset'] = ltrim($accountBalance['asset'], 'LD');
            }

            $userHasAsset = $this->user()->assetLogs()->whereHas('asset', function ($query) use ($accountBalance) {
                $query->where('symbol', $accountBalance['asset']);
            })->exists();

            if ($userHasAsset) {
                Log::info("Asset ({$accountBalance['asset']}) already exists!");
            } else {
                $totalBalanceQty = $accountBalance['free'] + $accountBalance['locked'];
                if ($totalBalanceQty > 0) {
                    $data = [
                        "platform_id" => Platform::whereName("Binance")->firstOrFail()->id,
                        "asset_id" => Asset::where('symbol', $accountBalance['asset'])->firstOrFail()->id,
                        "quantity_bought" => (string) $totalBalanceQty, // OR $asset['executedQty'] (is one of them)
                        "initial_value" => "0",
                    ];

                    Log::info('asset data', $data);

                    CreateLog::run($data);
                }
            }
        }
    }

//    private function purchase($asset)
//    {
//        $data = [
//            "platform_id" => Platform::whereName("Binance")->firstOrFail()->id,
//            "asset_id" => Asset::where('symbol', $this->currentAsset)->firstOrFail()->id,
//            "quantity_bought" => $asset['origQty'], // OR $asset['executedQty'] (is one of them)
//            "initial_value" => $asset['price'] * $asset['origQty'],
//            "date_of_purchase" => Carbon::make($asset['time'])->toDate(),
//        ];
//
//        CreateLog::make($data);
//    }
//
//
//    private function withdrawal($asset)
//    {
//        $getWithrawableLog = $this->user->assetLogs()->whereHas('asset', function ($query) {
//                $query->whereName($this->currentAsset);
//            })->where('quantity_bought', '>', $asset['origQty'])->first();
//
//        $data = [
//            'log_id' => $getWithrawableLog->id,
//            'value' => $asset['price'] * $asset['origQty'],
//            'quantity' => $asset['origQty'],
//            'date' => Carbon::make($asset['time'])->toDate(),
//        ];
//
//        CreateWithdrawal::make($data);
//    }


    private function buildUrl($asset = null)
    {
        $lastUpdate = $this->user()->fetched_remote_balance_at ?? now();
        $timestamp = Carbon::parse($lastUpdate)->getTimestampMs();

        $url = static::API_URL . $this->selectedEndpoint['path'];

        $queryString = "timestamp=$timestamp";
        if (count($this->selectedEndpoint['params'])) {
            foreach ($this->selectedEndpoint['params'] as $key => $value) {
                if ($value === "required") {
                    if ($key === "symbol") {
                        $queryString .= "&$key=$symbolPairs";
                    }

                    if ($key === "limit") {
                        $queryString .= "&$key=500";
                    }
                }
            }
        }

        $signature = hash_hmac("sha256", $queryString, $this->userApiKeys->secret);
        $url .= "?$queryString&signature=$signature";

        return $url;
    }
}
