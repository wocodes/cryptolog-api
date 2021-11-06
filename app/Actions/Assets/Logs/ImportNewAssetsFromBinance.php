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

    protected static $commandSignature = 'import:binance-logs {--user_id=}';

    private int $binanceServerTimestamp;
    private string $currentAsset;
    private int $countImport = 0;
    private ?Authenticatable $user = null;
    private $userApiKeys;
    private const API_URL = "https://api1.binance.com/api/v3";
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
     */
    public function handle()
    {
        $this->user = $this->user();

        if (!$this->user && $this->user_id) {
            $this->user = User::findOrFail($this->user_id);
        }

        Log::info("Importing Balance from Binance for User {$this->user->id}");

        $this->userApiKeys = $this->user->apiKeys()->first();
        if (!$this->userApiKeys) {
            Log::info("User has no api keys set. Can't import");
            return false;
        }

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
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());
//            throw ($throwable->getMessage());
        }

        // update fetched_remote_balances_at column
        $this->user->fetched_remote_balance_at = now();
        $this->user->save();

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

    private function logByAccountBalance(array $accountBalances)
    {
        foreach ($accountBalances as $accountBalance) {
            // check if asset is saved in Binance LenDing (i.e Savings)
            // if so, strip the LD prefix from the asset.
            if (substr($accountBalance['asset'], 0, 2) === 'LD') {
                $accountBalance['asset'] = ltrim($accountBalance['asset'], 'LD');
            }
            
            $userHasAsset = $this->user->assetLogs()->whereHas('asset', function ($query) use ($accountBalance) {
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
                        "current_quantity" => (string) $totalBalanceQty, // OR $asset['executedQty'] (is one of them)
                        "initial_value" => "0",
                        "user_id" => $this->user->id
                    ];

                    Log::info('asset data', $data);

                    CreateLog::run($data);
                }
            }
        }
    }


    private function buildUrl()
    {
//        $this->getBinanceServerTimestamp();
//
        $timestamp = now()->getTimestampMs() + 1000;
//        $humanTime = Carbon::createFromTimestampMs($timestamp)->toDateTimeString();
//        $bHumanTime = Carbon::createFromTimestampMs($this->binanceServerTimestamp)->toDateTimeString();
//
//        Log::info("My Server Time: $timestamp: $humanTime");
//        Log::info("Binance Server Time: $this->binanceServerTimestamp: $bHumanTime");

        $url = static::API_URL . $this->selectedEndpoint['path'];

        $queryString = "timestamp=$timestamp";
        $signature = hash_hmac("sha256", $queryString, $this->userApiKeys->secret);
        $url .= "?$queryString&signature=$signature";

        return $url;
    }
}
