<?php

namespace App\Actions\Waitlist;

use App\Models\Waitlist;
use App\Notifications\SendRegistrationNotification;
use App\Traits\JsonResponse;
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
            'email' => 'required|email:rfc,dns'
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
            Waitlist::create(['email' => $this->email]);

            Notification::route('mail', $this->email)->notifyNow(new SendRegistrationNotification($this->email));
        }

        return JsonResponse::success([], "Thanks for joining our waitlist.");
    }
}
