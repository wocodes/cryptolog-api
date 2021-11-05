<?php

namespace App\Actions\Admin\User;

use App\Models\User;
use App\Models\Waitlist;
use App\Notifications\SendInvitationNotification;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Action;

class SendInvite extends Action
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
            "id" => "integer|exists:waitlists"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = Waitlist::find($this->id);

        $this->createUserAccount($user);

        return $user;
    }

    private function createUserAccount(Waitlist $user)
    {
        $randomCharPassword = base64_encode(now());
        $data = [
            'name' => explode("@", $user->email)[0],
            'email' => $user->email,
            'password' => bcrypt($randomCharPassword)
        ];

        $user = User::create($data);

        $user->notifyNow(new SendInvitationNotification($randomCharPassword));
    }

    public function jsonResponse($user)
    {
        return JsonResponse::success(null, "Invite Sent to {$user->email}");
    }
}
