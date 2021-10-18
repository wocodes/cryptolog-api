<?php

namespace App\Actions\Shared\Fiat;

use App\Models\Fiat;
use App\Traits\JsonResponse;
use Lorisleiva\Actions\Action;

class ListFiats extends Action
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
        return Fiat::all();
    }

    public function jsonResponse($fiats)
    {
        return JsonResponse::success($fiats, "Fiats fetched");
    }
}
