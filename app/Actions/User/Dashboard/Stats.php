<?php

namespace App\Actions\User\Dashboard;

use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class Stats extends Action
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
     * ListPlatforms the validation rules that apply to the action.
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
        $data = [
            "assets_count" => $this->getTotalAssetsCount(),
            "assets_value" => $this->getTotalAssetsValue(),
            "assets_profit" => $this->getTotalAssetsProfit(),
            "assets_loss" => $this->getTotalAssetsLoss()
        ];
        return JsonResponse::success($data);
    }

    private function getTotalAssetsCount()
    {
        return $this->user()->assetLogs()->distinct('asset_id')->count();
    }

    private function getTotalAssetsValue()
    {
        return $this->user()->assetLogs()->select('current_value')->sum('current_value');
    }

    private function getTotalAssetsProfit()
    {
        return $this->user()->assetLogs()->where('profit_loss', '>', 0)->select('profit_loss')->sum('profit_loss');
    }

    private function getTotalAssetsLoss()
    {
        return $this->user()->assetLogs()->where('profit_loss', '<', 0)->select('profit_loss')->sum('profit_loss');
    }
}
