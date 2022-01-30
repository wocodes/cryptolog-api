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
        if (!Role::count()) {
            Role::create(['name' => 'admin', 'guard_name' => 'api']);
            Role::create(['name' => 'client', 'guard_name' => 'api']);
        }

        if (!Permission::count()) {
            Permission::create(['name' => 'log-asset', 'guard_name' => 'api']);
            Permission::create(['name' => 'co-own', 'guard_name' => 'api']);
            Permission::create(['name' => 'bot-trade', 'guard_name' => 'api']);
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
