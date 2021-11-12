<?php

namespace App\Actions\User\Auth;

use App\Exceptions\PasswordResetException;
use App\Models\User;
use App\Notifications\SendPasswordResetNotification;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class ResetPassword extends Action
{
    const VALID_REQUEST = "Request Validated.";
    const INVALID_TOKEN = "Invalid Request";
    const RESET_SUCCESSFUL = "Password Reset Successful. You may now login.";
    const PASSWORD_MISMATCH = "Passwords Aren't The Same";
    const WRONG_PASSWORDS = "Wrong Passwords";

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
            "token" => "nullable|string",
            "email" => "nullable|string|email:dns,rfc",
            "old_password" => "nullable|string",
            "new_password" => "nullable|required_with:confirm_password|string|min:6",
            "confirm_password" => "nullable|required_with:new_password|string|min:6",
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     * @throws PasswordResetException
     */
    public function handle()
    {
        if ($this->user()) {
            if (!password_verify($this->user()->password, bcrypt($this->old_password))) {
                throw new PasswordResetException(static::WRONG_PASSWORDS);
            }

            if ($this->new_password !== $this->confirm_password) {
                throw new PasswordResetException(static::PASSWORD_MISMATCH);
            }

            $this->user()->password = bcrypt($this->new_password);
            $this->user()->password_reset_token = null;
            $this->user()->save();

            return static::RESET_SUCCESSFUL;
        } elseif ($this->token && $this->new_password && $this->confirm_password) {
            if ($this->new_password !== $this->confirm_password) {
                throw new PasswordResetException(static::PASSWORD_MISMATCH);
            }

            $user = User::where('password_reset_token', $this->token)->first();

            if (!$user) {
                throw new PasswordResetException(static::INVALID_TOKEN);
            }

            $user->password = bcrypt($this->new_password);
            $user->password_reset_token = null;
            $user->save();

            return static::RESET_SUCCESSFUL;
        } elseif ($this->token) {
            $user = User::where('password_reset_token', $this->token)->first();

            if (!$user) {
                throw new PasswordResetException(static::INVALID_TOKEN);
            }

            return static::VALID_REQUEST;
        } elseif ($this->email) {
            $user = User::where('email', $this->email)->first();

            if (!$user) {
                throw new PasswordResetException(static::INVALID_TOKEN);
            }

            $user->password_reset_token = uniqid();
            $user->save();

            $user->notify(new SendPasswordResetNotification());

            return static::VALID_REQUEST;
        }
    }

    public function jsonResponse($response)
    {
        return JsonResponse::success([], $response);
    }
}
