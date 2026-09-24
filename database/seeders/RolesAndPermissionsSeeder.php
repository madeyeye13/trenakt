<?php

namespace Database\Seeders;

use App\Support\AdminPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the fixed permission catalog (see AdminPermissions) and the one
 * role the app structurally depends on: super_admin, which AppServiceProvider
 * grants a blanket Gate::before bypass so it always has full access even to
 * permissions added after it was created. Every other role - what it's
 * called, which permissions it holds, who is assigned it - is created by a
 * super admin through the Roles & Staff admin pages, never here.
 *
 * Safe to re-run: firstOrCreate throughout, so it only fills in whatever is
 * missing (e.g. after a new permission is added to the catalog).
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AdminPermissions::names() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(AdminPermissions::names());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
