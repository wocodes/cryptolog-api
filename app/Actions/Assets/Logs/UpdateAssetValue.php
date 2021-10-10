<?php

namespace App\Actions\Assets\Logs;

use App\Actions\Binance\GetAssets24hTicker;
use App\Models\Asset;
use App\Models\AssetLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class UpdateAssetValue extends Action implements ShouldQueue
{
    use Queueable;

    private Collection $logs;
    private array $currentAssetData = [];

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
        $this->logs = $this->getLogs();

        $this->currentAssetData = GetAssets24hTicker::run(['user_id' => $this->user()->id]);
        Log::info("current asset data", $this->currentAssetData);

        $this->updateLogs();
    }


    private function getLogs() : Collection
    {
        return AssetLog::all();
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

            $logs = $query->whereHas('asset', function ($query) use ($datum) {
                $query->where('symbol', $datum["symbol"]);
            })->where('is_sold', 0)->chunkById(100, function ($chunkedLogs) use ($datum) {
                foreach ($chunkedLogs as $chunkedLog) {
                    $usdtSellRate = $chunkedLog->user->fiat->usdt_sell_rate ?? 0;

                    $qtyBought = $chunkedLog->quantity_bought;
                    $bidPrice = $datum['bidPrice'];

                    $chunkedLog->current_value = $qtyBought * $bidPrice;
                    $chunkedLog->initial_value = (float) $chunkedLog->initial_value > 0 ? $chunkedLog->initial_value : $chunkedLog->current_value;
                    $chunkedLog->profit_loss = $chunkedLog->current_value - $chunkedLog->initial_value;
                    $chunkedLog->{'24_hr_change'} = $datum['priceChangePercent'];
                    $chunkedLog->roi = $chunkedLog->initial_value > 0 ? $chunkedLog->profit_loss / $chunkedLog->initial_value : 0;
                    $chunkedLog->daily_roi = $chunkedLog->roi > 0 ? $chunkedLog->roi / 3 : 0;
                    $chunkedLog->current_price = $datum['bidPrice'];
                    $chunkedLog->last_updated_at = now();
                    $chunkedLog->profit_loss_fiat = $chunkedLog->profit_loss * $usdtSellRate ?? 0;

                    $chunkedLog->save();
                }
            });
        }
    }
}
