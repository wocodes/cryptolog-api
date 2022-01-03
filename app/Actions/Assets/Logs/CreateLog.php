<?php

namespace App\Actions\Assets\Logs;

use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Platform;
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
//            "platform.id" => "required_without:platform.name|integer",
            "platform.id" => "nullable|integer",
//            "platform.name" => "required_without:platform.id|string",
            "platform.name" => "nullable|string",
//            "location.id" => "required_without:location.name|integer",
            "location.id" => "nullable|integer",
//            "location.name" => "required_without:location.id|string",
            "location.name" => "nullable|string",
            "currency_type" => "required|string",
            "asset_id" => "required|integer",
            "quantity_bought" => "required|string",
            "initial_value" => "required|string",
            "date_of_purchase" => "nullable|date",
            "detail" => "nullable|string",
            "first_ever" => "nullable|boolean",
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

        if (!empty($this->platform['name'])) {
            $platform = $this->saveNewPlatformData();
        }

        if (!empty($this->location['name'])) {
            $location = $this->saveNewLocationData();
        }

        // Execute the action.
        $logData = [
            "platform_id" => !empty($this->platform['id']) ? $this->platform['id'] : $platform->id,
            "asset_id" => $this->asset_id,
            "quantity_bought" => $this->quantity_bought,
            "current_quantity" => $this->current_quantity ?? $this->quantity_bought,
            "date_bought" => $this->date_of_purchase,
            "detail" => $this->detail,
            "asset_location_id" => !empty($this->location['id']) ? $this->location['id'] : (!empty($this->location['name']) ? $location->id : null)
        ];

        $usdtSellRate = $this->user()->fiat->usdt_sell_rate ?? 0;

        if ($this->currency_type === 'fiat') {
            $logData["initial_value"] = $this->initial_value / $usdtSellRate;
            $logData["current_value"] = $this->initial_value / $usdtSellRate;
            $logData["initial_value_fiat"] = $this->initial_value;
            $logData["current_value_fiat"] = $this->initial_value;
        } else {
            $logData["initial_value"] = $this->initial_value;
            $logData["current_value"] = $this->initial_value;
            $logData["initial_value_fiat"] = $this->initial_value * $usdtSellRate;
            $logData["current_value_fiat"] = $this->initial_value * $usdtSellRate;
        }

        $this->user->assetLogs()->create($logData);

        if($this->first_ever) {
            $this->user()->finished_setup = 1;
            $this->user()->save();
        }

        return response()->json(["message" => 'success'], 201);
    }

    private function saveNewPlatformData(): Platform
    {
        $platform = Platform::firstOrCreate(['name' => $this->platform['name']]);

        // attach to asset_type_id
        $assetTypeId = Asset::findOrFail($this->asset_id)->assetType->id;
        $platform->assetTypes()->attach([$assetTypeId]);

        return $platform;
    }

    private function saveNewLocationData(): AssetLocation
    {
        return AssetLocation::firstOrCreate(
            ['name' => $this->location['name']],
            ['interest_rate' => $this->interest_rate]
        );
    }
}
