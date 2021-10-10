<?php

namespace App\Actions\User;

use App\Actions\Assets\Logs\ImportFromBinance;
use App\Actions\Assets\Logs\UpdateAssetValue;
use App\Events\ApiKeysSaved;
use App\Models\Platform;
use App\Traits\JsonResponse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class SaveApiKeys extends Action implements ShouldQueue
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
            "key" => "required|string",
            "secret" => "required|string",
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->user()->apiKeys()->firstOrCreate([
            'platform_id' => Platform::whereName('Binance')->firstOrFail()->id,
            'key' => $this->key,
            'secret' => $this->secret,
        ]);

        $this->user()->finished_setup = 1;
        $this->user()->save();

        // now fetch data from binance!
        ImportFromBinance::run();

        // update the logs
        UpdateAssetValue::run();

        return true;
    }

    public function jsonResponse()
    {
        return JsonResponse::success([], 'User Api Keys Saved');
    }
}
