<?php

namespace App\Actions\User;

use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class Update extends Action
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
            'fiat_id' => 'nullable|integer'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->fiat_id) {
            $this->user()->fiat_id = $this->fiat_id;
        }

        $this->user()->save();
    }

    public function jsonResponse()
    {
        return JsonResponse::success([], "User Detail Updated");
    }
}
