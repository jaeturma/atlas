<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
            'settings.manage',
            'devices.manage',
            'logs.download',
            'logs.upload',
            'dtr.manage',
            'activities.manage',
            'leaves.manage',
            'leaves.approve',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions($permissions);

        $superadminRole = Role::firstOrCreate([
            'name' => 'Superadmin',
            'guard_name' => 'web',
        ]);

        $superadminRole->syncPermissions($permissions);

        $adminUser = \App\Models\User::where('email', 'admin@admin.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('Admin');
        }

        $dtrRole = Role::firstOrCreate([
            'name' => 'DTR Incharge',
            'guard_name' => 'web',
        ]);

        $dtrRole->syncPermissions([
            'dtr.manage',
            'activities.manage',
        ]);

        $leaveInchargeRole = Role::firstOrCreate([
            'name' => 'Leave Incharge',
            'guard_name' => 'web',
        ]);

        $leaveInchargeRole->syncPermissions([
            'leaves.manage',
            'leaves.approve',
        ]);

        $leaveApproverRole = Role::firstOrCreate([
            'name' => 'Leave Approver',
            'guard_name' => 'web',
        ]);

        $leaveApproverRole->syncPermissions([
            'leaves.approve',
        ]);

        Role::firstOrCreate([
            'name' => 'Employee',
            'guard_name' => 'web',
        ]);
    }
}
