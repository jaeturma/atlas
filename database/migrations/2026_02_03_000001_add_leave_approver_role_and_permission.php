<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'leaves.approve',
            'guard_name' => 'web',
        ]);

        $leaveApproverRole = Role::firstOrCreate([
            'name' => 'Leave Approver',
            'guard_name' => 'web',
        ]);

        $leaveApproverRole->givePermissionTo($permission);

        $leaveInchargeRole = Role::firstOrCreate([
            'name' => 'Leave Incharge',
            'guard_name' => 'web',
        ]);

        $leaveInchargeRole->givePermissionTo($permission);

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }

        $superadminRole = Role::where('name', 'Superadmin')->first();
        if ($superadminRole) {
            $superadminRole->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'leaves.approve')->first();
        if ($permission) {
            $roles = Role::whereIn('name', [
                'Leave Approver',
                'Leave Incharge',
                'Admin',
                'Superadmin',
            ])->get();

            foreach ($roles as $role) {
                $role->revokePermissionTo($permission);
            }

            $permission->delete();
        }

        Role::where('name', 'Leave Approver')->delete();
    }
};
