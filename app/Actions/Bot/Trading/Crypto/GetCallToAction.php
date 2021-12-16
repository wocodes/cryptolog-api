<?php

namespace App\Actions\Bot\Trading\Crypto;

use App\Models\User;
use Binance\API;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class GetCallToAction extends Action
{
    private array $fiveMinsTicker;
    private array $tenMinsTicker;
    private $api;
    private $lastOrderType; // temporary storage for last order
    private ?User $user = null;
    private array $availablebalances = [];
    private array $tradeableSymbols = ['XRP'];
    /**
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
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
        $this->initializeUserAndApiKeys();

        $this->api = new API($this->userApiKeys->key, $this->userApiKeys->secret);
        $this->availablebalances = $this->api->balances(true);

//        Cache::forever("last_order", "SELL");
//        dd(1);
        foreach ($this->tradeableSymbols as $theSymbol) {
            // temporary storage for last order
            $this->lastOrderType = Cache::get("last_order");
            if(!$this->lastOrderType) {
                Cache::forever("last_order", "SELL");
            }
            
            $symbol = "{$theSymbol}USDT";
    
    
            Log::info("");
            Log::info("--- Running Bot (Trading $symbol) ---");
    
            // MA (5)
            $this->fiveMinsTicker = $this->getMovingAverage($symbol, "15m", 6);
    
            // MA (10)
            $this->tenMinsTicker = $this->getMovingAverage($symbol, "15m",  11);
    
            Log::info("MA(5): {$this->fiveMinsTicker['moving_average']} -- MA(10): {$this->tenMinsTicker['moving_average']}");
            Log::info("Last Order: ($this->lastOrderType)");
            
            
            Log::info("Available USDT {$this->availablebalances['USDT']['available']}");
            // dump($this->availablebalances['USDT']['available'] > 10);
            $usdtBalance = $this->availablebalances['USDT']['available']; // $500

            if(($this->fiveMinsTicker['last_tick_open'] > $this->fiveMinsTicker['moving_average']) &&
                ($this->fiveMinsTicker['last_tick_open'] < $this->fiveMinsTicker['last_tick_close']) &&
//                ($this->fiveMinsTicker['moving_average'] < $this->tenMinsTicker['moving_average']) &&
                $this->lastOrderType == "SELL"
            ) {
                Log::info("Now is time to buy... :-)");
                $this->lastOrderType = "BUY";
                Cache::forever("last_order", "BUY");
                
                if ($usdtBalance > 10) {
                    $sellPercentage = 100; // recommended is 20 i.e 20%
    
                    // for all assets place a trade of 20% of total USDT value as BUY Order
    //                $quantity = (($usdtBalance/100) * $sellPercentage) / $this->tenMinsTicker; // gives $100. $100 worth of this asset gives total quantity of 2196000
                    $price =(($usdtBalance/100) * $sellPercentage); // gives $100
    
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
                    Log::alert("BUY:: Available USDT Balance {$usdtBalance} is low. Can't place an order.");
                }
            } elseif (
                ($this->fiveMinsTicker['last_tick_close'] < $this->fiveMinsTicker['moving_average']) &&
                ($this->fiveMinsTicker['last_tick_open'] > $this->fiveMinsTicker['last_tick_close']) &&
//                ($this->fiveMinsTicker['moving_average'] > $this->tenMinsTicker['moving_average']) &&
                $this->lastOrderType == "BUY"
            ) {
                Log::info("Now is time to sell... :-(");
                $this->lastOrderType = "SELL";
                Cache::forever("last_order", "SELL");
    
                // $sellPercentage = 20;

                // for all assets place a trade of 20% of total USDT value as BUY Order
                // $quantity = (($usdtBalance/100) * $sellPercentage) / $this->tenMinsTicker; // gives $100. $100 worth of this asset gives total quantity of 2196000
                // $price =(($usdtBalance/100) * $sellPercentage); // gives $100

                Log::info("Available $symbol qty:", [(string) $this->availablebalances[trim($symbol, 'USDT')]['available']]);

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
                Log::info("Not time to place an order... Still checking");
            }
        }
    }

    private function getMovingAverage(string $symbol, string $timeFrame, int $limit)
    {
        $ticks = $this->api->candlesticks($symbol, $timeFrame, $limit);
        $closingPrices = array_column($ticks, 'close');
        $openPrices = array_column($ticks, 'open');

        // if ($limit === 6) { 
             array_pop($closingPrices);
             array_pop($openPrices);
            Log::info("Open At: " . end($openPrices));
            Log::info("Closed At: " . end($closingPrices));
        // }

        return [
            "last_tick_open" => end($openPrices),
            "last_tick_close" => end($closingPrices),
            "moving_average" => number_format(array_sum($closingPrices) / count($closingPrices), 8)
        ];
    }
}
