<?php

namespace App\Actions\Platforms;

use App\Models\Platform;
use Lorisleiva\Actions\Action;

class CreatePlatform extends Action
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
     * ListPlatforms the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => "required|string",
            "asset_type_id" => "nullable|integer"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $platform = Platform::create(['name' => $this->name]);

        if ($this->asset_type_id) {
            $platform->assetTypes()->attach([$this->asset_type_id]);
        }

        return $platform;
    }
}
