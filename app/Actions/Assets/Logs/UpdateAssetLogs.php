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
     * ListPlatforms the validation rules that apply to the action.
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
            $activeApi = $asset->assetType->activeApi;

            $pair = "{$assetSymbol}{$this->baseSymbol}";
            $url = $activeApi->host . "/ticker/24hr?symbol={$pair}";

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

            $logs = $query->whereHas('asset', function($query) use($datum) {
                $query->where('symbol', $datum["symbol"]);
            })->where('is_sold', 0)->chunkById(100, function ($chunkedLogs) use ($datum) {
                foreach ($chunkedLogs as $chunkedLog) {
                    $qtyBought = $chunkedLog->quantity_bought;
                    $bidPrice = $datum['bidPrice'];

                    $chunkedLog->current_value = $qtyBought * $bidPrice;
                    $chunkedLog->profit_loss = $chunkedLog->current_value - $chunkedLog->initial_value;
                    $chunkedLog->{'24_hr_change'} = $datum['priceChangePercent'];
                    $chunkedLog->roi = $chunkedLog->profit_loss / $chunkedLog->initial_value;
                    $chunkedLog->daily_roi = $chunkedLog->roi / 3;
                    $chunkedLog->current_price = $datum['bidPrice'];
                    $chunkedLog->last_updated_at = now();
                    $chunkedLog->profit_loss_fiat = $chunkedLog->profit_loss * $chunkedLog->user->fiat->usdt_sell_rate ?? null;

                    $chunkedLog->save();
                }


//                LEFT FOR REFERENCE PURPOSE
//                update([
//                    "current_value" => DB::raw("`quantity_bought` * {$datum['bidPrice']}"),
//                    "profit_loss" => DB::raw("`quantity_bought` * {$datum['bidPrice']} - `initial_value`"),
//                    "24_hr_change" => $datum['priceChangePercent'],
//                    "roi" => (DB::raw("`quantity_bought` * {$datum['bidPrice']} - `initial_value` / `initial_value`")),
//                    "daily_roi" => DB::raw("`quantity_bought` * {$datum['bidPrice']} - `initial_value` / `initial_value`  / 3"),
//                    "current_price" => $datum['bidPrice'],
//                    "last_updated_at" => now(),
//                    "profit_loss_naira" => DB::raw("`profit_loss` * 530")
//                ]);
            });
        }
    }
}
