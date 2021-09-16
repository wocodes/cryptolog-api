<?php

namespace App\Actions\Admin\Asset;

use App\Models\Asset;
use Lorisleiva\Actions\Action;

class ListAsset extends Action
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
        return Asset::all();
    }

    public function jsonResponse($assets)
    {
        $data = [
            "message" => "List of Assets",
            "data" => $assets
        ];

        return response()->json($data);
    }
}
