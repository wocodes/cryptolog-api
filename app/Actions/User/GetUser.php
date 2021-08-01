<?php

namespace App\Actions\User;

use Lorisleiva\Actions\Action;

class GetUser extends Action
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
        $data = [
            "data" => $this->user(),
            "message" => "User data fetched successfully",
            "success" => true,
        ];

        return response()->json($data);
    }
}
