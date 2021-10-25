<?php

namespace App\Actions\Assets\Logs;

use Lorisleiva\Actions\Action;

class WorstPerforming extends Action
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
        return $this->user()
            ->assetLogs()
            ->where('is_sold', 0)
            ->where('profit_loss', '<', 0)
            ->orderBy('profit_loss', 'ASC')
            ->limit(5)
            ->with('asset.assetType', 'platform', 'withdrawals')
            ->paginate(10);
    }
}
