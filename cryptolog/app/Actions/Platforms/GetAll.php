<?php

namespace App\Actions\Platforms;

use App\Models\Platform;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class GetAll extends Action
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
        return response()->json(Platform::all()->toArray());
    }
}
