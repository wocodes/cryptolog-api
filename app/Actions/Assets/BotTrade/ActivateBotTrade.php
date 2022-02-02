<?php

namespace App\Actions\Assets\BotTrade;

use App\Models\Asset;
use App\Models\BotTrade;
use App\Models\User;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;
use Spatie\Permission\Models\Permission;

class ActivateBotTrade extends Action
{
    private array $errors = [];
    private array $data = [];

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return ($this->user()->id === $this->user_id) ||
            ($this->user()->is_admin && $this->user()->id !== $this->user_id);
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "user_id" => "required|integer",
            "asset_id" => "nullable|integer",
            "mode" => "required|string|in:auto,manual",
            "trading_amount" => "required|numeric|min:" . floor($this->user()->fiat->usdt_buy_rate * env('MIN_TRADING_AMOUNT_USD'))
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle(): bool
    {
        $assetId = $this->asset_id ?? Asset::whereIn('symbol', ['XRP', 'SHIB'])->get()->random()->id;

        $user = User::findOrFail($this->user_id);
        $wallet = $user->wallet;
        $totalBillable = (double) (env('TRADING_BOT_FEE') + $this->trading_amount);

        // check the users wallet balance and no active subscription in bot_trades
        // $this->trading_amount is a sum of user's trading
        $hasMinAvailableBalance = $wallet && $wallet->current_balance >= $totalBillable;
//        $activeSubscription = $user->botTradeAssets()->where('mode', 'auto')->where('is_active', 1)->exists();

        if (!$user->hasPermissionTo('bot-trade')) {
            if ($hasMinAvailableBalance) {
//            if ($hasMinAvailableBalance && !$activeSubscription) {
                $user->wallet()->update(['current_balance' => $wallet->current_balance - $totalBillable]);
                $botTradePermission = Permission::findByName('bot-trade');

                $user->givePermissionTo($botTradePermission);

                $assetBotTrade = $user->botTradeAssets()->where('asset_id', $assetId)->first();

                if ($user->botTradeAssets()->where('is_active', 1)->count() == 2) {
                    $this->errors[] = "Can't activate Bot trade for more than 2 assets at the moment.";

                    return false;
                }

                if (!$assetBotTrade || $assetBotTrade && !$assetBotTrade->is_active) {
                    $botTrade = $user->botTradeAssets()->where('asset_id', $assetId)->first();

                    if(!$botTrade) {
                        $botTrade = new BotTrade();
                        $botTrade->user_id = $user->id;
                        $botTrade->asset_id = $assetId;
                    }

                    $value = $this->trading_amount / $user->fiat->usdt_buy_rate;
                    $botTrade->is_active = 1;
                    $botTrade->initial_value += $value;
                    $botTrade->current_value += $value;
                    $botTrade->save();

                    $this->data[] = "Preparing your trades... Should be active in less than 24hrs";

                    return true;
                }
            } else {
                $this->errors[] = "Not enough wallet balance to subscribe. Please fund your wallet.";

                return false;
            }
        }

        return true;
    }

    public function response($result)
    {
        if ($result) {
            return JsonResponse::success($this->data, "Activated Asset AI Trade.");
        }

        return JsonResponse::error($this->errors, "Couldn't activate AI trade");
    }
}
