<?php

namespace App\Actions\Assets\Locations;

use App\Models\AssetLocation;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class GetLocation extends Action
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
        return AssetLocation::where('name', 'LIKE', "%{$this->search}%")->get()->take(10);
    }

    public function jsonResponse($result)
    {
        return JsonResponse::success($result);
    }
}
