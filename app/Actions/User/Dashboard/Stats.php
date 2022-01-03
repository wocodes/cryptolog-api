<?php

namespace App\Actions\User\Dashboard;

use App\Models\Asset;
use App\Models\AssetType;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\Log;
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

    private function assetQuery()
    {
        $user = $this->user();

        $userAssets = $user->assetLogs();

        if ($this->asset_type && $this->asset_type !== 'All') {
            $userAssets = $userAssets->whereHas('asset', function ($query) {
                $query->whereHas('assetType', function ($query) {
                    $query->where('name', AssetType::ASSET_NAMES[$this->asset_type]);
                });
            });
        }

        return $userAssets;
    }

    private function getTotalAssetsCount()
    {
        return $this->assetQuery()->distinct('asset_id')->count();
    }

    private function getTotalAssetsValue()
    {
        return [
            "usd" => $this->assetQuery()->select('current_value')->sum('current_value'),
            "fiat" => $this->assetQuery()->select('current_value_fiat')->sum('current_value_fiat')
        ];
    }

    private function getTotalAssetsProfit()
    {
        $query = $this->assetQuery()->where('profit_loss', '>', 0);
        return [
            "usd" => $query->select('profit_loss')->sum('profit_loss'),
            "fiat" => $query->select('profit_loss_fiat')->sum('profit_loss_fiat')
        ];
    }

    private function getTotalAssetsLoss()
    {
        $query = $this->assetQuery()->where('profit_loss', '<', 0);
        return [
            "usd" => $query->select('profit_loss')->sum('profit_loss'),
            "fiat" => $query->select('profit_loss_fiat')->sum('profit_loss_fiat')
        ];
    }
}
