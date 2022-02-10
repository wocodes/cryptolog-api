<?php

namespace App\Actions\Bot\Trading\Crypto;

use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class GetLogs extends Action
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
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $botTrade = $this->user()->botTradeAssets()->where('mode', 'auto')->first();

        if($botTrade) {
            return $botTrade->logs()->latest()->paginate();
        }
    }

    public function response($result)
    {
        return JsonResponse::success($result, "Bot Trade Results");
    }
}
