<?php

namespace App\Actions\User\Auth;

use App\Models\User;
use Lorisleiva\Actions\Action;

class Register extends Action
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
            'name' => $this->name,
            'email' => $this->username,
            'password' => bcrypt($this->password)
        ];

        $user = User::create($data);

        return response()->json($user);
    }
}