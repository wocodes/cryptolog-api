<?php

namespace App\Actions\Admin\User;

use App\Models\Waitlist;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class GetWaitlist extends Action
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
        return Waitlist::all();
    }

    public function jsonResponse($users)
    {
        return JsonResponse::success($users, "Waitlist of Users");
    }
}
