<?php

namespace App\Actions\Assets\Logs;

use App\Models\AssetLog;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class CreateWithdrawal extends Action
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
            'log_id' => 'required|exists:asset_logs,id',
            'value' => 'required|numeric',
            'quantity' => 'required|numeric',
            'date' => 'nullable|date',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $log = AssetLog::findOrFail($this->log_id);

        $withdrawal = $log->withdrawals()->create([
            'initial_value' => $this->value,
            'current_value' => $this->value,
            'quantity' => $this->quantity,
            'date' => $this->date
        ]);

        Log::info("Logged withdrawal for {$log->asset->symbol}");

        Log::info("Subtracting quantity");
        $log->quantity_bought = $log->quantity_bought - $this->quantity;
        $log->save();

        Log::info("completed logged withdrawal");
//        $this->user()->fetched_remote_orders_at = now();
//        $this->user()->save();

//        return $withdrawal;
    }

    public function jsonResponse($withdrawal)
    {
        return JsonResponse::success($withdrawal->toArray());
    }
}
