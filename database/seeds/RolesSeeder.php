<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = ["admin", "client"];
        foreach ($roles as $role) {
            Role::findOrCreate($role, 'api');
        }

        $freePermissions = ["log-asset", "calculator"];
        $paidPermissions = ["co-own-asset", "bot-trade", "trading-tips"];
        $allPermissions = array_merge($freePermissions, $paidPermissions);
        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        $this->assignPermissionsToRole();
    }

    // assign permissions to roles
    public function assignPermissionsToRole()
    {
        $clientRole = Role::findByName('client', 'api');

        $clientRole->givePermissionTo('log-asset');
    }
}
