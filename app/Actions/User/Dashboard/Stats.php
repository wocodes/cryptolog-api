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
        $data = [
            "assets_count" => $this->getTotalAssets(),
            "assets_value" => $this->getTotalAssetsValue()
        ];
        return JsonResponse::success($data);
    }

    private function getTotalAssets()
    {
        return $this->user()->assetLogs()->distinct('asset_id')->count();
    }

    private function getTotalAssetsValue()
    {
        return $this->user()->assetLogs()->select('current_value')->sum('current_value');
    }
}
