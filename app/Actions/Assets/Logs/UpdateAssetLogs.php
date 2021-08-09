<?php

namespace App\Actions\Assets\Logs;

use App\Models\Asset;
use App\Models\AssetLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class UpdateAssetLogs extends Action
{
    private Collection $logs;
    private Collection $assets;
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
        $this->assets = $this->getAssets();

        $this->logs = $this->getLogs();

        $this->getCurrentAssetData();

        $this->updateLogs();
    }


    private function getAssets() : Collection
    {
        return Asset::all();
    }


    private function getLogs() : Collection
    {
        return AssetLog::all();
    }


    private function getCurrentAssetData() : void
    {
        foreach($this->assets as $asset) {
            $assetSymbol = $asset->symbol;
            $pair = "{$assetSymbol}{$this->baseSymbol}";
            $url = "https://api3.binance.com/api/v3/ticker/24hr?symbol={$pair}";

            $data = Http::retry(3)->get($url);

            $assetData = $data->json();

            Log::info("Fetching update for {$pair}", [$assetData]);

            $this->currentAssetData[$assetSymbol] = array_merge($assetData, ["symbol" => $assetSymbol]);
        }
    }


    private function updateLogs()
    {


        foreach($this->currentAssetData as $datum)
        {
            if($this->user()) {
                $query = $this->user()->assetLogs();
            } else {
                $query = AssetLog::query();
            }

            $query->whereHas('asset', function($query) use($datum) {
                $query->where('symbol', $datum["symbol"]);
            })->update([
                "current_value" => DB::raw("`quantity_bought` * {$datum['bidPrice']}"),
                "profit_loss" => DB::raw("`quantity_bought` * {$datum['bidPrice']} - `initial_value`"),
                "24_hr_change" => $datum['priceChangePercent'],
                "roi" => (DB::raw("`quantity_bought` * {$datum['bidPrice']} - `initial_value` / `initial_value`")),
                "daily_roi" => DB::raw("`quantity_bought` * {$datum['bidPrice']} - `initial_value` / `initial_value`  / 3"),
                "current_price" => $datum['bidPrice'],
                "last_updated_at" => now(),
                "profit_loss_naira" => DB::raw("`quantity_bought` * {$datum['bidPrice']} - `initial_value` * 500")
            ]);
        }
    }
}
