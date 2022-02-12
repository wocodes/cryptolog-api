<?php

namespace App\Actions\User\Permissions;

use App\Models\User;
use Lorisleiva\Actions\Action;
use Spatie\Permission\Models\Permission;

class GrantPermission extends Action
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
            "permissions" => "array|nullable",
            "all_permissions" => "nullable|boolean",
            "user_id" => "required|integer"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $thePermissions = $this->all_permissions ? Permission::all()->pluck('name') : $this->permissions;
        $user = User::findOrFail($this->user_id);

        foreach ($thePermissions as $permission) {
            $botTradePermission = Permission::findByName($permission, 'api');

            $user->givePermissionTo($botTradePermission);
        }

        return $user->permissions;
    }
}
