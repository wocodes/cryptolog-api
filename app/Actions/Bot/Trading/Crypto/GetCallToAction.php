<?php

namespace App\Actions\Bot\Trading\Crypto;

use App\Models\User;
use Binance\API;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class GetCallToAction extends Action
{
    private ?string $fiveMinsMovingAverage;
    private ?string $tenMinsMovingAverage;
    private $api;
    private $lastOrderType; // temporary storage for last order
    private ?User $user = null;
    private array $availablebalances = [];
    /**
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    private $userApiKeys;


    public function __construct()
    {
        parent::__construct();

        $this->initializeUserAndApiKeys();

        $this->api = new API($this->userApiKeys->key, $this->userApiKeys->secret);
        $this->availablebalances = $this->api->balances(true);

        // temporary storage for last order
        $this->lastOrderType = Cache::get("last_order");
        if(!$this->lastOrderType) {
            Cache::forever("last_order", "SELL");
        }
    }

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


    private function initializeUserAndApiKeys()
    {
        $this->user = $this->user();

        if (!$this->user && $this->user_id) {
            $this->user = User::findOrFail($this->user_id);
        }

        $this->user = User::where('is_admin', 0)->where('email', 'william.odiomonafe@gmail.com')->firstOrFail();
        Log::info("Gotten User", [$this->user]);

        $this->userApiKeys = $this->user->apiKeys()->first();
        if (!$this->userApiKeys) {
            Log::info("User has no api keys set. Can't import");
            return false;
        }
    }



    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $symbol = "BTCUSDT";


        Log::info("");
        Log::info("--- Running Bot (Trading $symbol) ---");

        // MA (5)
        $this->fiveMinsMovingAverage = $this->getMovingAverage($symbol, "15m", 6);

        // MA (10)
        $this->tenMinsMovingAverage = $this->getMovingAverage($symbol, "15m",  11);

        Log::info("MA(5): $this->fiveMinsMovingAverage -- MA(10): $this->tenMinsMovingAverage");
        Log::info("Last Order: ($this->lastOrderType)");
        if($this->fiveMinsMovingAverage > $this->tenMinsMovingAverage && $this->lastOrderType == "SELL") {
            Log::info("Now is time to buy... :-)");
            $this->lastOrderType = "BUY";
            Cache::forever("last_order", "BUY");

            if ($this->availablebalances['USDT'] > 10) {
                $userUsdtBalance = $this->availablebalances['USDT']; // $500
                $sellPercentage = 100; // recommended is 20 i.e 20%

                // for all assets place a trade of 20% of total USDT value as BUY Order
//                $quantity = (($userUsdtBalance/100) * $sellPercentage) / $this->tenMinsMovingAverage; // gives $100. $100 worth of this asset gives total quantity of 2196000
                $price =(($userUsdtBalance/100) * $sellPercentage); // gives $100

                $response = PlaceOrder::make([
                    "symbol" => $symbol,
                    "side" => "BUY",
                    "quoteOrderQty" => (string) $price,
//            "quantity" => (string) $quantity,
//            "price" => $assetPrice,
//            "newClientOrderId" => uniqid(),
//            "type" => "LIMIT",
//            "timeInForce" => "GTC",
                    "type" => "MARKET", // This specifies that the order should be filled immediately at the current market price
                    "newOrderRespType" => "ACK", // Sends an ACKnowledgement that a new order has been filled
                ])->run();

                Log::info("Order response:", [$response]);
            } else {
                Log::alert("BUY:: Available USDT Balance {$this->availablebalances['USDT']} is low. Can't place an order.");
            }
        } elseif ($this->fiveMinsMovingAverage < $this->tenMinsMovingAverage && $this->lastOrderType == "BUY") {
            Log::info("Now is time to sell... :-(");
            $this->lastOrderType = "SELL";
            Cache::forever("last_order", "SELL");

            if ($this->availablebalances['USDT'] > 10) {
                $userUsdtBalance = $this->availableUsdt; // $500
                $sellPercentage = 20;

                // for all assets place a trade of 20% of total USDT value as BUY Order
                $quantity = (($userUsdtBalance/100) * $sellPercentage) / $this->tenMinsMovingAverage; // gives $100. $100 worth of this asset gives total quantity of 2196000
                $price =(($userUsdtBalance/100) * $sellPercentage); // gives $100

                $response = PlaceOrder::make([
                    "symbol" => $symbol,
                    "side" => "SELL",
//                    "quoteOrderQty" => (string) $price,
            "quantity" => (string) $this->availablebalances[trim($symbol, 'USDT')]['available'],
//            "price" => $assetPrice,
//            "newClientOrderId" => uniqid(),
//            "type" => "LIMIT",
//            "timeInForce" => "GTC",
                    "type" => "MARKET", // This specifies that the order should be filled immediately at the current market price
                    "newOrderRespType" => "ACK", // Sends an ACKnowledgement that a new order has been filled
                ])->run();

                Log::info("Order response:", [$response]);
            } else {
                Log::alert("SELL: Available USDT Balance {$this->availableUsdt} is low. Can't place an order.");
            }
        } else {
            Log::info("Not time to place an order... Still checking");
        }
    }

    private function getMovingAverage(string $symbol, string $timeFrame, int $limit)
    {
        $ticks = $this->api->candlesticks($symbol, $timeFrame, $limit);
        $closingPrices = array_column($ticks, 'close');
        array_pop($closingPrices);

        return number_format(array_sum($closingPrices) / count($closingPrices), 8);
    }
}
