<?php

namespace App\Actions\Waitlist;

use App\Models\User;
use App\Models\Waitlist;
use App\Notifications\SendRegistrationNotification;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Action;

class Add extends Action
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
            'email' => 'required|email:rfc,dns',
            'ref' => 'nullable|string'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $emailExists = Waitlist::whereEmail($this->email)->first();

        if(!$emailExists) {
            $data = ['email' => $this->email];

            if($this->ref) {
                $data['referred_by'] = User::where('referral_code', $this->ref)->first()->id;
            }

            $waitlist = Waitlist::create($data);

            if($waitlist) {
                Notification::route('mail', $this->email)->notifyNow(new SendRegistrationNotification($this->email));

                return JsonResponse::success([], "Thanks. You will receive an Invite.");
            }

            return JsonResponse::error([], "Couldn't subscribe to waitlist. Pls try again");
        }

        return JsonResponse::success([], "You are already subscribed.");
    }
}
