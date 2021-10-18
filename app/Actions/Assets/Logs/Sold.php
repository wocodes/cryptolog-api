<?php

namespace App\Actions\Assets\Logs;

use App\Models\AssetLog;
use Lorisleiva\Actions\Action;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class Sold extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->id === AssetLog::findOrFail($this->id)->user_id;
    }

    /**
     * ListPlatforms the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|numeric|exists:asset_logs,id'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $log = AssetLog::find($this->id);

        if ($log->is_sold) {
            throw new BadRequestException("Invalid request", 501);
        }

        $log->is_sold = 1;
        $log->save();
    }

    public function jsonResponse()
    {
        $data = [
            'message' => "Marked as Sold",
        ];

        return response()->json($data);
    }
}
