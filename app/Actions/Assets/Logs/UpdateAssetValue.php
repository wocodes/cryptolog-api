<?php

namespace App\Actions\Assets\Logs;

use App\Actions\Binance\GetAssets24hTicker;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class UpdateAssetValue extends Action implements ShouldQueue
{
    use Queueable;

    protected static $commandSignature = 'update:value {--user_id=}';

    private ?Authenticatable $user = null;
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
        return [
            "user_id" => "nullable|integer"
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

        Log::info("Updating Asset Values for User {$this->user->id}");

        $this->currentAssetData = GetAssets24hTicker::run(['user_id' => $this->user->id]);

        $this->updateLogs();
    }


    private function updateLogs()
    {
        foreach($this->currentAssetData as $datum)
        {
            $query = $this->user->assetLogs();

            $query->whereHas('asset', function ($query) use ($datum) {
                    $query->where('symbol', $datum["symbol"]);
                })->where('is_sold', 0)->chunkById(100, function ($chunkedLogs) use ($datum) {
                    foreach ($chunkedLogs as $chunkedLog) {
                        $usdtSellRate = $chunkedLog->user->fiat->usdt_sell_rate ?? 0;

                        $qtyBought = $chunkedLog->quantity_bought;
                        $bidPrice = $datum['bidPrice'];

                        $chunkedLog->current_value = $qtyBought * $bidPrice;
                        $chunkedLog->current_value_fiat = $chunkedLog->current_value * $usdtSellRate ?? 0;
                        $chunkedLog->initial_value = (float) $chunkedLog->initial_value > 0 ? $chunkedLog->initial_value : $chunkedLog->current_value;
                        $chunkedLog->initial_value_fiat = $chunkedLog->initial_value * $usdtSellRate ?? 0;
                        $chunkedLog->profit_loss = $chunkedLog->current_value - $chunkedLog->initial_value;
                        $chunkedLog->{'24_hr_change'} = $datum['priceChangePercent'];
                        $chunkedLog->roi = $chunkedLog->initial_value > 0 ? $chunkedLog->profit_loss / $chunkedLog->initial_value : 0;
                        $chunkedLog->daily_roi = $chunkedLog->roi > 0 ? $chunkedLog->roi / 3 : 0;
                        $chunkedLog->current_price = $datum['bidPrice'];
                        $chunkedLog->last_updated_at = now();
                        $chunkedLog->profit_loss_fiat = $chunkedLog->profit_loss * $usdtSellRate ?? 0;

                        if($chunkedLog->initial_value > 1 && $chunkedLog->current_value < 1 ) {
                            $chunkedLog->is_sold = 1;
                        }

                        $chunkedLog->save();
                    }
                });
        }
    }
}
