<?php

namespace App\Actions\Platforms;

use App\Models\Asset;
use App\Models\Platform;
use App\Traits\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Action;

class Get extends Action
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
        return [
            "asset_id" => "nullable|integer"
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
        if($this->asset_id) {
            $asset = Asset::findOrFail($this->asset_id);

            return $asset->platforms;
        }

        return Platform::all();

    }

    public function jsonResponse($platforms)
    {
        return JsonResponse::success( $platforms, "Fetched platforms list");
    }
}
