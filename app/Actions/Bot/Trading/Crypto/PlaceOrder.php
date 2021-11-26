<?php

namespace App\Actions\Bot\Trading\Crypto;

use App\Models\User;
use Binance\API;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class PlaceOrder extends Action
{

    private const API_URL = "https://api.binance.com/api/v3";
    private $user;
    private $userApiKeys;

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
            "symbol" => "required|string",
            "side" => "required|string",
            "quantity" => "required_without:quoteOrderQty|string",
            "quoteOrderQty" => "required_without:quantity|string",
            "price" => "nullable|string",
            "newClientOrderId" => "nullable|string",
            "newOrderRespType" => "nullable|string",
            "timeInForce" => "nullable|string",
            "type" => "nullable|string",
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

        $this->user = User::where('is_admin', 0)->firstOrFail();

//        Log::info("Importing Balance from Binance for User {$this->user->id}");

        $this->userApiKeys = $this->user->apiKeys()->first();
        if (!$this->userApiKeys) {
            Log::info("User has no api keys set. Can't import");
            return false;
        }

        try {
//            $url = $this->buildUrl();
            $qty = $this->quantity . " qty" ?? "$" . $this->quoteOrderQty;

            Log::info("Placing {$this->side} {$this->type} Order for {$qty} of {$this->symbol}");
//            $assets = Http::withHeaders(["X-MBX-APIKEY" => $this->userApiKeys->key])->retry(3)->post($url)->json();

            $api = new API($this->userApiKeys->key, $this->userApiKeys->secret);

            if ($this->side === 'BUY') {
                $order = $this->quantity ? $api->MarketBuy($this->symbol, $this->quantity) :
                    $api->MarketQuoteBuy($this->symbol, $this->quoteOrderQty);
            } else {
                $order = $this->quantity ? $api->MarketSell($this->symbol, $this->quantity) :
                    $api->MarketQuoteSell($this->symbol, $this->quoteOrderQty);
            }

            Log::info('result', [$order]);
        } catch (RequestException $requestException) {
            Log::error("An error occurred.", [$requestException->response]);
            Log::error("Code", [$requestException->response['code']]);
            Log::error("Message", [$requestException->response['msg']]);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());
        }
    }


    private function buildUrl()
    {
//        $timestamp = now()->getTimestampMs() + 1000;
////        $url = static::API_URL . "/order";
//        $url = static::API_URL . "/order/test";
//
//        $queryString = "symbol={$this->symbol}";
//        $queryString .= "&side={$this->side}";
//        $queryString .= "&type={$this->type}";
//
//        if($this->timeInForce) $queryString .= "&timeInForce={$this->timeInForce}";
//
////        $queryString .= "&quantity={$this->quantity}";
//        $queryString .= "&quoteOrderQty=100";
//
//        if($this->price) $queryString .= "&price=" . number_format($this->price, 8);
//        if($this->newClientOrderId) $queryString .= "&newClientOrderId={$this->newClientOrderId}";
//
////        if($this->newOrderRespType) $queryString .= "&newOrderRespType={$this->newOrderRespType}";
//
//        $queryString .= "&timestamp=$timestamp";
//
//        $signature = hash_hmac("sha256", $queryString, $this->userApiKeys->secret);
//        $url .= "?$queryString&signature=$signature";

//        return $url;
    }
}
