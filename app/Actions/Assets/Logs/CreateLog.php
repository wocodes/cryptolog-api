<?php

namespace App\Actions\Assets\Logs;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class CreateLog extends Action
{
    private ?Authenticatable $user;

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
            "user_id" => "nullable|integer",
            "platform_id" => "required_without:platform_name|integer",
            "platform_name" => "required_without:platform_id|string",
            "asset_id" => "required|integer",
            "quantity_bought" => "required|string",
            "initial_value" => "required|string",
            "date_of_purchase" => "nullable|date",
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->user = $this->user();

        if (!$this->user && $this->user_id) {
            $this->user = User::findOrFail($this->user_id);
        }

        if ($this->platform_name) {
            $platform = CreateLog::make(['name' => $this->platform_name]);

            // attach to asset_type_id
            $assetTypeId = Asset::findOrFail($this->asset_id)->asset_type->id;
            $platform->assetTypes()->attach([$assetTypeId]);
        }

        // Execute the action.
        $this->user->assetLogs()->create([
            "platform_id" => $this->platform_id ?? $platform->id,
            "asset_id" => $this->asset_id,
            "quantity_bought" => $this->quantity_bought,
            "current_quantity" => $this->current_quantity,
            "initial_value" => $this->initial_value,
            "current_value" => $this->initial_value,
            "date_bought" => $this->date_of_purchase
        ]);

        return response()->json(["message" => 'success'], 201);
    }
}
