<?php

namespace App\Actions\Admin\Asset\Ownership;

use Lorisleiva\Actions\Action;

class CreateNewAsset extends Action
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
            "asset_id" => "required|numeric", // e.g bitcoin, vacant land,
            "name" => "required|string",
            "description" => "required|string",
            "units" => "required|numeric",
            "sub_units" => "required|numeric",
            "start_date" => "required|date",
            "end_date" => "required|date",
            "active" => "required|integer"
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
    }
}
