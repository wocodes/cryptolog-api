<?php

namespace App\Actions\Admin\Asset;

use App\Models\Asset;
use Lorisleiva\Actions\Action;

class CreateAsset extends Action
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
            'type_id' => 'required|numeric',
            'name' => 'required|string|unique:assets,name',
            'symbol' => 'required|string|unique:assets,name',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        Asset::create([
            'name' => $this->name,
            'symbol' => $this->symbol,
            'asset_type_id' => $this->type_id,
        ]);

        $data = [
            "message" => "New Asset Added",
            "data" => []
        ];

        return response()->json($data, 201);
    }
}
