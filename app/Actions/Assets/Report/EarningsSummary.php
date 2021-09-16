<?php

namespace App\Actions\Assets\Report;

use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Action;

class EarningsSummary extends Action
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
        $assetLogs = $this->user()->assetLogs()->select('asset_id', 'current_value')->get()->groupBy('asset.name')->toArray();

        $data = [];
        foreach($assetLogs as $key => $assetLog) {
            foreach($assetLog as $log) {
                $data[$key]["value"] = ($data[$key]["value"] ?? 0) + $log['current_value'];
            }
        }

        return JsonResponse::success($data);
    }
}
