<?php

namespace App\Actions\Assets\Report;

use App\Models\Asset;
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
        $data = [];
        $assets = Asset::whereHas("logs", function ($query) {
            $query->where('user_id', $this->user()->id)
                ->where('current_value', '>', 0);
        })->select('id', 'name', 'symbol', 'icon')->get()->toArray();

        foreach ($assets as $asset) {
            $log = $this->user()->assetLogs()->where('asset_id', $asset['id']);

            $data[] = [
                "name" => $asset['name'],
                "icon" => $asset['icon'] ?? null,
                "symbol" => $asset['symbol'],
                "qty" => $log->sum('current_quantity'),
                "current_value" => $log->sum('current_value'),
                "current_value_fiat" => $log->sum('current_value_fiat'),
                "percent_change" => $log->sum('24_hr_change')
            ];
        }





//        $data = [];
//        foreach($assetLogs as $key => $assetLog) {
//            foreach($assetLog as $log) {
//                $data[$key]["value"] = ($data[$key]["value"] ?? 0) + $log['current_value'];
//            }
//        }

        return JsonResponse::success($data);
    }
}
