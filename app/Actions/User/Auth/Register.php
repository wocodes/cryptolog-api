<?php

namespace App\Actions\User\Auth;

use App\Events\RegistrationSuccessful;
use App\Listeners\AcknowledgeRegistration;
use App\Models\User;
use App\Notifications\SendVerificationEmail;
use App\Traits\JsonResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
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
     * ListPlatforms the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => "required|string",
//            "username" => "required|email:rfc,dns",
            "username" => "required|email",
            'ref' => 'nullable|string',
            "password" => "required|string|min:5",
            "phone" => "nullable|string"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $userExists = User::whereEmail($this->username)->exists();

        if ($userExists) {
            return JsonResponse::error([], "Account already exists. Please login.");
        }

        $refCode = User::where('referral_code', $this->ref)
            ->orWhere('email', $this->ref)->first();

        if (!$refCode || ($refCode && $this->ref == $this->username)) {
            return JsonResponse::error([], "Invalid Referral Code");
        }

        try {
            $data = [
                'name' => $this->name,
                'email' => $this->username,
                'phone' => $this->phone,
                'password' => bcrypt($this->password)
            ];

            if ($this->ref) {
                $data['referred_by'] = User::where('referral_code', $this->ref)
                    ->orWhere('email', $this->ref)
                    ->first()->id;
            }

            $registered = User::create($data);

            if ($registered) {
                RegistrationSuccessful::dispatch($registered);

                return JsonResponse::success([], "Successfully registered.");
            }
        } catch (QueryException $exception) {
            throw new \Exception("Something went wrong.");
        } catch (\Throwable $throwable) {
            throw new \Exception($throwable->getMessage());
        }


        return JsonResponse::error([], "Couldn't register at this moment. Pls try again");
    }
}
