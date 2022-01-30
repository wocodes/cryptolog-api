<?php

namespace App\Actions\Utility;

use App\Models\AssetLog;
use Lorisleiva\Actions\Action;

class PruneAssetLogs extends Action
{
    protected static $commandSignature = "logs:prune {--user_id=}";

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
            "user_id" => "nullable"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {

        $query = AssetLog::query();

        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }

        $countQuery = $query->count();
        $log = "$countQuery logs pruned";
        $log .= $this->user_id ? " for User ID: {$this->user_id}" : "";
        $this->getCommandInstance()->info($log);

        $query->forceDelete();
    }
}
