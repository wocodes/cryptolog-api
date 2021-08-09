<?php

namespace App\Actions\User\Metas;

use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class Save extends Action
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
            "metas" => 'required|array'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        // Execute the action.
        foreach($this->metas as $setting) {
            $this->user()->metas()->updateOrCreate(['name' => $setting['id']], ['value' => $setting['value']]);
        }

        return JsonResponse::success([],'Updated Successfully',200);
    }
}
