<?php

namespace App\Actions\User\Wallet;

use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class GetWallet extends Action
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
        return $this->user()->wallet()->select('current_balance')->first();
    }

    public function response($result)
    {
        $data = [
            'current_balance' => $result['current_balance'] ?? 0,
            'fiat' => $this->user()->fiat()->select('id','country_code','symbol','usdt_sell_rate','usdt_buy_rate','short_symbol')->first()
        ];

        return JsonResponse::success($data, "Wallet Retrieved");
    }
}
