<?php

namespace App\Actions\User\Metas;

use App\Models\User;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class Get extends Action
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
        // Execute the action.
        $metas = User::METAS;

        $meta = $this->user()->metas()->select('name', 'value')->get();

        $mapped_metas = $meta->mapWithKeys(function ($item) {
            return [$item['name'] => $item['value']];
        });

        foreach ($mapped_metas as $key => $val) {
            $metas[$key] = $val;
        }

        return JsonResponse::success($metas,null, 200);
    }
}