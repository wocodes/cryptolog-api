<?php

namespace App\Actions\AssetTypes;

use App\Models\AssetType;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class ReadAll extends Action
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
        return AssetType::all();
    }

    public function jsonResponse($assets)
    {
        return JsonResponse::success($assets);
    }
}
