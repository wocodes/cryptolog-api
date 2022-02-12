<?php

namespace App\Actions\User\AppSubscription;

use App\Models\AppSubscription;
use App\Models\User;
use Lorisleiva\Actions\Action;

class SaveNewSubscription extends Action
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
            "start_date" => "nullable|date",
            "end_date" => "required|date",
            "user_id" => "required"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::findOrFail($this->user_id);

        return $user->appSubscriptions()->create([
            "start_date" => $this->start_date ?? now(),
            "end_date" => $this->end_date,
            "is_active" => 1
        ]);
    }
}
