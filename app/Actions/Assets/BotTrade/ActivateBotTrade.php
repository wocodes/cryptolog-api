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
            "mode" => "required|string|in:auto,manual"
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

        // check the users wallet balance and no active subscription in bot_trades
        // PLEASE REMOVE 550 MAGIC NUMBER IN FUTURE UPDATE,
        // Value is subscription fee (500) + 10000 (min. trading amount)
        $hasMinAvailableBalance = $wallet && $wallet->current_balance >= (double) 11000;
//        $activeSubscription = $user->botTradeAssets()->where('mode', 'auto')->where('is_active', 1)->exists();

        if (!$user->hasPermissionTo('bot-trade')) {
            if ($hasMinAvailableBalance) {
//            if ($hasMinAvailableBalance && !$activeSubscription) {
                $user->wallet()->update(['current_balance' => $wallet->current_balance - (double) 11000]);
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

                    $value = 10000 / $user->fiat->usdt_buy_rate;
                    $botTrade->is_active = 1;
                    $botTrade->initial_value += $value;
                    $botTrade->current_value += $value;
                    $botTrade->save();

                    return true;
                }
            } else {
                $this->errors[] = "Not enough wallet balance (min. NGN11,000) to subscribe. Please fund your wallet.";

                return false;
            }
        }

        return true;
    }

    public function response($result)
    {
        if ($result) {
            return JsonResponse::success([], "Activated Asset AI Trade");
        }

        return JsonResponse::error($this->errors, "Couldn't activate AI trade");
    }
}
