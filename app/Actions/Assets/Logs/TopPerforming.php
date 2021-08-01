<?php

namespace App\Actions\Assets\Logs;

use Lorisleiva\Actions\Action;

class TopPerforming extends Action
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
            ->where('profit_loss', '>', 0)
            ->orderBy('profit_loss', 'DESC')
            ->limit(5)
            ->with('asset.assetType','platform')
            ->paginate(10);
    }
}
