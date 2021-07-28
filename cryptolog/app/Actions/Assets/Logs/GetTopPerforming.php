<?php

namespace App\Actions\Assets\Logs;

use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class GetTopPerforming extends Action
{
    use JsonResponse;

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
        $topPerforming = $this->user()->assetLogs()->with('asset.assetType','platform')->get()->toArray();

        return JsonResponse::success($topPerforming,"Fetched Top Performing Assets");
    }
}
