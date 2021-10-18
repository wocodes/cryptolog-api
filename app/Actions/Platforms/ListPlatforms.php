<?php

namespace App\Actions\Platforms;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Platform;
use App\Traits\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Action;

class ListPlatforms extends Action
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
    public function handle(): Collection
    {
        // Execute the action.
        if ($this->asset_type_id) {
            $assetType = AssetType::findOrFail($this->asset_type_id);

            return $assetType->platforms;
        }

        return Platform::all();
    }

    public function jsonResponse($platforms)
    {
        return JsonResponse::success($platforms, "Fetched platforms list");
    }
}
