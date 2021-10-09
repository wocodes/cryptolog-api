<?php

namespace App\Actions\Binance;

use App\Models\Asset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class GetAssets24hTicker extends Action
{
    private array $currentAssetData = [];
    private $baseSymbol = "USDT";

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
        $assets = Asset::all();
        foreach ($assets as $asset) {
            $assetSymbol = $asset->symbol;
            $activeApi = $asset->assetType->activeApi;

            $pair = "{$assetSymbol}{$this->baseSymbol}";
            $url = $activeApi->host . "/ticker/24hr?symbol={$pair}";

            $data = Http::retry(3)->get($url);

            $assetData = $data->json();

            Log::info("Fetching update for {$pair}", [$assetData]);

            $this->currentAssetData[$assetSymbol] = array_merge($assetData, ["symbol" => $assetSymbol]);
        }

        return $this->currentAssetData;
    }
}
