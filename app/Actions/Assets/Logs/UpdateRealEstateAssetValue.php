<?php

namespace App\Actions\Assets\Logs;

use App\Actions\Binance\GetAssets24hTicker;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class UpdateRealEstateAssetValue extends Action implements ShouldQueue
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

        Log::info("Updating Real Estate Asset Values for User {$this->user->id}");

        $this->updateRealEstateLogs();
    }


    private function updateRealEstateLogs()
    {
        $query = $this->user->assetLogs();

        $query->whereHas('asset', function ($query) {
                $query->whereHas('assetType', function ($query) {
                            $query->where('name', 'Real Estate');
                        });
            })->where('is_sold', 0)->chunkById(100, function ($chunkedLogs) {
                foreach ($chunkedLogs as $chunkedLog) {
                    $usdtSellRate = $chunkedLog->user->fiat->usdt_sell_rate ?? 0;

                    $yearlyInterestRate = $chunkedLog->location->interest_rate;
                    $daysDifference = Carbon::today()->diffInDays($chunkedLog->date_bought);
                    $dailyInterestRate = $chunkedLog->location->interest_rate / 365;
                    $interestAccrued = ($chunkedLog->current_value_fiat / 100) * $dailyInterestRate;
                    $totalCurrentValuePlusInterest = $chunkedLog->initial_value_fiat + ($interestAccrued * $daysDifference);

                    $chunkedLog->current_value_fiat = $totalCurrentValuePlusInterest;
                    $chunkedLog->current_value = $chunkedLog->current_value_fiat / $usdtSellRate ?? 0;
                    $chunkedLog->initial_value = $chunkedLog->initial_value_fiat / $usdtSellRate ?? 0;
                    $chunkedLog->profit_loss = $chunkedLog->current_value - $chunkedLog->initial_value;
                    $chunkedLog->{'24_hr_change'} = $dailyInterestRate;
                    $chunkedLog->roi = $chunkedLog->initial_value > 0 ? $chunkedLog->profit_loss / $chunkedLog->initial_value : 0;
                    $chunkedLog->daily_roi = $chunkedLog->roi > 0 ? $chunkedLog->roi / 3 : 0;
                    $chunkedLog->current_price = $chunkedLog->current_value_fiat;
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
