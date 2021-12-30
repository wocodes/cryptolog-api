<?php

namespace App\Actions\User\Dashboard;

use App\Models\Asset;
use App\Models\AssetType;
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
        $user = $this->user();

        if ($this->asset_type) {
            $userAssets = $user->assetLogs()->whereHas('asset', function ($query) {
                $query->whereHas('assetType', function ($query) {
                    $query->where('name', AssetType::ASSET_NAMES[$this->asset_type]);
                });
            });
        }


        $data = [
            "assets_count" => $this->getTotalAssetsCount($userAssets),
            "assets_value" => $this->getTotalAssetsValue($userAssets),
            "assets_profit" => $this->getTotalAssetsProfit($userAssets),
            "assets_loss" => $this->getTotalAssetsLoss($userAssets)
        ];
        return JsonResponse::success($data);
    }

    private function getTotalAssetsCount($userAssets)
    {
        return $userAssets->distinct('asset_id')->count();
    }

    private function getTotalAssetsValue($userAssets)
    {
        return [
            "usd" => $userAssets->select('current_value')->sum('current_value'),
            "fiat" => $userAssets->select('current_value_fiat')->sum('current_value_fiat')
        ];
    }

    private function getTotalAssetsProfit($userAssets)
    {
        $query = $userAssets->where('profit_loss', '>', 0);
        return [
            "usd" => $query->select('profit_loss')->sum('profit_loss'),
            "fiat" => $query->select('profit_loss_fiat')->sum('profit_loss_fiat')
        ];
    }

    private function getTotalAssetsLoss($userAssets)
    {
        $query = $userAssets->where('profit_loss', '<', 0);
        return [
            "usd" => $query->select('profit_loss')->sum('profit_loss'),
            "fiat" => $query->select('profit_loss_fiat')->sum('profit_loss_fiat')
        ];
    }
}
