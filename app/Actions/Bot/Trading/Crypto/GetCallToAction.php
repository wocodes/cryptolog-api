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
    private array $availableBalances = [];
    private array $tradeableSymbols;
    private int $minimumUsdtBalance = 10;
    /**
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    private $userApiKeys;

    private int $buyPercentage;


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
        $aiTradeSubscribedUsers = User::whereHas('botTradeAssets', function ($query) {
           $query->where('is_active', 1);
        })->get();
        
        foreach ($aiTradeSubscribedUsers as $user) {
            $this->tradeableSymbols = $user->botTradeAssets()->where('is_active', 1)
                ->with('asset:id,name')
                ->get()
                ->pluck('asset.name')->toArray();

            // check if should auto trade or manual
            $shouldAutoBotTrade = $user->botTradeAssets()->where('is_active', 1)->where('mode', 'auto')->exists();

            $this->initializeUserAndApiKeys($user, $shouldAutoBotTrade);
            $this->availableBalances = $this->api->balances(true);

            $this->resetAllOrderStatus($user);
            $countOfSymbolsInBuy = $this->countSymbolsInBuy($user);

            foreach ($this->tradeableSymbols as $theSymbol) {
                $this->setInitialTradeOrder($user, $theSymbol);

                $symbol = "{$theSymbol}USDT";
                Log::info("");
                Log::info("--- Running Bot (Trading $symbol) ---");

                $this->getMovingAverages($symbol);

                Log::info("Last Order: ($this->lastOrderType)");
                Log::info("Available USDT {$this->availableBalances['USDT']['available']}");
                $usdtBalance = $this->availableBalances['USDT']['available']; // $500

                if ($this->hasBuyCondition()) {

                    $this->cacheTriggeredOrder($user, $theSymbol, "BUY");
                    $this->placeBuyOrder($usdtBalance, $countOfSymbolsInBuy, $symbol);

                } elseif ($this->hasSellCondition()) {

                    $this->cacheTriggeredOrder($user, $theSymbol, "SELL");
                    $this->placeSellOrder($usdtBalance, $countOfSymbolsInBuy, $symbol);

                } else {
                    Log::info("Not time to place an order... Still checking");
                }
            }
        }
    }



    private function placeBuyOrder($usdtBalance, $countOfSymbolsInBuy, $symbol)
    {
        if ($usdtBalance > $this->minimumUsdtBalance || $countOfSymbolsInBuy < count($this->tradeableSymbols)) {
            $this->buyPercentage = 100 / count($this->tradeableSymbols) - $countOfSymbolsInBuy; // recommended is 20 i.e 20%

            // for all assets place a trade of 20% of total USDT value as BUY Order
//                        $quantity = (($usdtBalance/100) * $sellPercentage) / $this->tenMinsTicker; // gives $100. $100 worth of this asset gives total quantity of 2196000
            $price = (($usdtBalance/100) * $this->buyPercentage); // gives $100

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
    }



    public function placeSellOrder($usdtBalance, $countOfSymbolsInBuy, $symbol)
    {
        // $sellPercentage = 20;

        // for all assets place a trade of 20% of total USDT value as BUY Order
        // $quantity = (($usdtBalance/100) * $sellPercentage) / $this->tenMinsTicker; // gives $100. $100 worth of this asset gives total quantity of 2196000
        // $price =(($usdtBalance/100) * $sellPercentage); // gives $100

        $availableSymbolQty = $this->availableBalances[str_replace('USDT', '', $symbol)]['available'];
        Log::info("Available $symbol qty:", [(string) $availableSymbolQty]);

        $response = PlaceOrder::make([
            "symbol" => $symbol,
            "side" => "SELL",
//                    "quoteOrderQty" => (string) $price,
            "quantity" => (string) $availableSymbolQty,
//            "price" => $assetPrice,
//            "newClientOrderId" => uniqid(),
//            "type" => "LIMIT",
//            "timeInForce" => "GTC",
            "type" => "MARKET", // This specifies that the order should be filled immediately at the current market price
            "newOrderRespType" => "ACK", // Sends an ACKnowledgement that a new order has been filled
        ])->run();

        Log::info("Order response:", [$response]);
    }



    private function initializeUserAndApiKeys(User $user, bool $autoTradeMode)
    {
        $this->user = $user;
        Log::info("Gotten User", [$this->user]);

        $adminApiKeys = User::where('email', 'william.odiomonafe@gmail.com')->first()->apiKeys()->first();
        $this->userApiKeys = $autoTradeMode ? $adminApiKeys : $this->user->apiKeys()->first();

        if (!$this->userApiKeys) {
            Log::info("User: {$this->user->email} ({$this->user->id}) has no api keys set. Can't import");
            return false;
        }

        $this->api = new API($this->userApiKeys->key, $this->userApiKeys->secret);
    }


    private function countSymbolsInBuy($user): int
    {
        $countOfSymbolsInBuy = 0;
        foreach ($this->tradeableSymbols as $theSymbol) {
            if (Cache::get("user_{$user->id}_{$theSymbol}_last_order") == "BUY") {
                $countOfSymbolsInBuy++;
            }
        }

        return $countOfSymbolsInBuy;
    }


    private function setInitialTradeOrder($user, $theSymbol)
    {
        // temporary storage for last order
        $this->lastOrderType = Cache::get("user_{$user->id}_{$theSymbol}_last_order");

        if (!$this->lastOrderType) {
            Cache::forever("user_{$user->id}_{$theSymbol}_last_order", "SELL");
        }

    }

    private function getMovingAverages($symbol)
    {
        $this->fiveMinsTicker = $this->getMovingAverage($symbol, "15m", 6); // MA (5)
        $this->tenMinsTicker = $this->getMovingAverage($symbol, "15m", 11); // MA (10)

        $log = "MA(5): {$this->fiveMinsTicker['moving_average']} -- MA(10): {$this->tenMinsTicker['moving_average']}";
        Log::info($log);
    }


    private function cacheTriggeredOrder(User $user, string $theSymbol, string $orderType)
    {
        $emoji = $orderType === "BUY" ? ":-)" : ":-(";

        Log::info("Now is time to $orderType... $emoji");
        $this->lastOrderType = $orderType;
        Cache::forever("user_{$user->id}_{$theSymbol}_last_order", $orderType);
    }


    private function hasBuyCondition()
    {
        return ($this->fiveMinsTicker['last_tick_open'] > $this->fiveMinsTicker['moving_average']) &&
            ($this->fiveMinsTicker['last_tick_open'] < $this->fiveMinsTicker['last_tick_close']) &&
//            ($this->fiveMinsTicker['moving_average'] < $this->tenMinsTicker['moving_average']) &&
            $this->lastOrderType == "SELL";
    }

    private function hasSellCondition()
    {

        return ($this->fiveMinsTicker['last_tick_close'] < $this->fiveMinsTicker['moving_average']) &&
            ($this->fiveMinsTicker['last_tick_open'] > $this->fiveMinsTicker['last_tick_close']) &&
//            ($this->fiveMinsTicker['moving_average'] > $this->tenMinsTicker['moving_average']) &&
            $this->lastOrderType == "BUY";
    }

    private function resetAllOrderStatus($user)
    {
        Log::info("Resetting all symbol order to default SELL status.");
        foreach ($this->tradeableSymbols as $symbol) {
            if (Cache::missing("user_{$user->id}_{$symbol}_last_order")) {
                Log::info("user_{$user->id}_{$symbol} doesn't have any trade history. Setting it to sell.");
                Cache::forever("user_{$user->id}_{$symbol}_last_order", "SELL");
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
