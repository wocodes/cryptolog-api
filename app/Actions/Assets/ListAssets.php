<?php

namespace App\Actions\Assets;

use App\Models\Asset;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class ListAssets extends Action
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
        return [
            "asset_type_id" => "nullable|integer"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        // Execute the action.
        $assets = Asset::query();

        if ($this->asset_type_id) {
            $assets =  $assets->where('asset_type_id', $this->asset_type_id);
        }

        return $assets->get();

    }

    public function jsonResponse($assets)
    {
        return JsonResponse::success( $assets, "Fetched assets list");
    }
}
