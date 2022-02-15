<?php

namespace App\Actions\Assets\BotTrade;

use App\Models\User;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;
use Spatie\Permission\Models\Permission;

class CheckStatus extends Action
{
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
            "user_id" => "nullable|integer",
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = $this->user_id ? User::findOrFail($this->user_id) : $this->user();

        return $user->hasPermissionTo('bot-trade') && $user->hasActivePaidSubscription() ? $user->botTradeAssets : false;
    }


    public function response($result)
    {
        return JsonResponse::success($result, "Asset AI Trade Status");
    }
}
