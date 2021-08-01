<?php

namespace App\Actions\User\Auth;

use App\Models\User;
use Lorisleiva\Actions\Action;

class Login extends Action
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
            "username" => "required|string",
            "password" => "required|string|min:5"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::where(['email' => $this->username])->first();

        if(!$user) {
            $response = [
                "data" => null,
                "message" => "User not found",
                "success" => false
            ];

            return response()->json($response, 404);
        }

        if(!password_verify($this->password, $user->password)) {
            $response = [
                "data" => null,
                "message" => "Invalid credentials",
                "success" => false
            ];

            return response()->json($response, 400);
        }
        $user->token = $user->createToken('user-token')->accessToken;

        $user = $user->only('id', 'name', 'email', 'token');
        $response = ['data' => $user, "message" => "Successfully logged in", 'success' => true];

        return response()->json($response);
    }
}
