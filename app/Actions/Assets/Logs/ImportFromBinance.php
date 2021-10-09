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

class ImportFromBinance extends Action implements ShouldQueue
{
    use Queueable;

    private int $binanceServerTimestamp;
    private array $currentLiveTickerData = [];
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
        $this->currentLiveTickerData = GetAssets24hTicker::run();
        $this->getBinanceServerTimestamp();
        $this->user = $this->user();
        $this->userApiKeys = $this->user->apiKeys()->first();

        $assets = Asset::all();

        foreach ($assets as $asset) {
            $url = $this->buildUrl($asset);
//            Log::info('url', [$url]);

            try {
                $assets = Http::withHeaders(["X-MBX-APIKEY" => $this->userApiKeys->key])->get($url)->json();

                if ($this->selectedEndpoint === 'all_orders') {
//                    Log::info('logging all orders');
                    $this->logByOrders($assets);
                } else {
//                    Log::info('logging all balances');
                    $this->logByAccountBalance($assets['balances']);
                    $this->countImport++;
                }
            } catch (RequestException $requestException) {
                throw new \HttpRequestException($requestException->getMessage());
            } catch (\Throwable $throwable) {
                dd($throwable->getMessage());
            }
        }

        return $this->countImport;
    }

    /**
     * NOTE: Observations!
     * 1. From findings, it was noticed that Binance requires usage of their current server time (ms timestamp) in order to
     * get orders via the query string, and this server time, when parsed via Carbon to get human date, returns a date in year 2000.
     * This seems to be incorrect somehow. Also, I observed that the most recent orders matches the most recent orders on the platform
     * BUT when the date of the transaction is parsed through Carbon, it returns a date in year 2000-2009 or so.
     *
     * 2. The initial plan was to get ALL the users past orders since they started using the platform, but Binance only allows fetching
     * data from the past 3 months which is a bummer! :-( Therefore an alternative approach is to get the user's wallet balance of each
     * crypto instead. Not cool enough though, but it's a good start anyway.
     *
     * @throws \Exception
     */
    public function asCommand()
    {
        $this->handle();
    }

    /**
     * @throws \HttpRequestException
     * @throws \Exception
     */
    private function getBinanceServerTimestamp()
    {
        try {
            // get current server time
            $response = Http::get(static::API_URL . "/time")->json();
            $this->binanceServerTimestamp = $response['serverTime'];
        } catch (RequestException $requestException) {
            throw new \HttpRequestException($requestException->getMessage());
        } catch (\Throwable $throwable) {
            throw new \ErrorException($throwable->getMessage());
        }
    }

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
        $balance = array_filter($accountBalances, function ($accountBalance) {
           return $accountBalance['asset'] === $this->currentAsset;
        });

        $balance = array_values($balance)[0];

        $totalBalanceQty = $balance['free'] + $balance['locked'];
        $currentAssetBidPrice = $this->currentLiveTickerData[$this->currentAsset]['bidPrice'];

        $data = [
            "platform_id" => Platform::whereName("Binance")->firstOrFail()->id,
            "asset_id" => Asset::where('symbol', $this->currentAsset)->firstOrFail()->id,
            "quantity_bought" => (string) $totalBalanceQty, // OR $asset['executedQty'] (is one of them)
            "initial_value" => (string) ($totalBalanceQty * $currentAssetBidPrice),
        ];

        CreateLog::run($data);
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
            })->where('quantity_bought', '>', $asset['origQty'])->first();

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
        $timestamp = $this->binanceServerTimestamp;

        $url = static::API_URL . $this->selectedEndpoint['path'];

        Log::info("Binance Current Server Time/Date", [Carbon::createFromTimestamp($timestamp)->toDateString()]);
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
