<?php

namespace App\Actions\Assets\Logs;

use App\Traits\JsonResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Action;

class GetLogs extends Action
{
    use JsonResponse;

    private const TOP_PERFORMING = "top-performing";
    private const WORST_PERFORMING =  "worst-performing";

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
        $logModes = [
            self::TOP_PERFORMING,
            self::WORST_PERFORMING
        ];

        return [
            "mode" => [
                "nullable",
                Rule::in($logModes)
            ]
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        switch($this->mode) {
            case "top-performing":
                $logs = $this->delegateTo(TopPerforming::class);
                break;

            case "worst-performing":
                $logs = $this->delegateTo(WorstPerforming::class);
                break;

            default:
                $active = $this->user()->assetLogs()->where('is_sold', 0)->where('current_value', '>', 1.00)->with('asset.assetType', 'platform', 'withdrawals')->get()->toArray();
                $noValue = $this->user()->assetLogs()->where('is_sold', 0)->where('current_value', 0.00)->with('asset.assetType', 'platform', 'withdrawals')->get()->toArray();
                $sold = $this->user()->assetLogs()->where('is_sold', 1)->with('asset.assetType', 'platform', 'withdrawals')->get()->toArray();
                $logs = array_merge($active, $noValue, $sold);
                break;
        }

        $mode = "";
        if($this->mode) {
            $mode = ucwords(str_replace("-", " ", $this->mode));
        }

        return JsonResponse::success($logs,"Fetched {$mode} Assets");
    }
}
