<?php

namespace App\Actions\Assets\Logs;

use Lorisleiva\Actions\Action;

class Create extends Action
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
        // Execute the action.
        $this->user()->assetLogs()->create([
            "platform_id" => $this->platform_id,
            "asset_id" => $this->asset_id,
            "quantity_bought" => $this->quantity_bought,
            "initial_value" => $this->initial_value,
            "date_bought" => $this->date_of_purchase
        ]);

        return response()->json(["message" => 'success'], 201);
    }
}
