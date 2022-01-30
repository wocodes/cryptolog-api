<?php

namespace App\Actions\Assets\BotTrade;

use App\Models\User;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class DeactivateBotTrade extends Action
{
    private array $errors;

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
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle(): bool
    {
        $user = User::findOrFail($this->user_id);
        $assetBotTrade = $user->botTradeAssets()->where('asset_id', $this->asset_id)->first();

        if ($assetBotTrade && $assetBotTrade->is_active) {
            $assetBotTrade->update(['is_active' => 0]);
            return true;
        }

        $this->errors[] = "Unable to deactivate AI trade";
        return false;
    }

    public function response($result)
    {
        if ($result) {
            return JsonResponse::success([], "Deactivated Asset AI Trade");
        }

        return JsonResponse::error($this->errors, "Couldn't deactivate AI trade");
    }
}
