<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RolesAndPermissionSeeder extends Seeder
{
   public function run(): void
{
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Permissions
    $permissions = [
        // Products
        'view products', 'create products', 'edit products', 'delete products',

        // Inventory
        'view inventory', 'create inventory', 'edit inventory', 'delete inventory',

        // Patients
        'view patients', 'create patients', 'edit patients', 'delete patients',

        // Prescriptions
        'view prescriptions', 'create prescriptions', 'edit prescriptions', 'delete prescriptions',

        // Sales
        'view sales', 'create sales',

        // Reports
        'view reports',

        // Purchase Orders
        'create purchase orders', 'view purchase orders', 'approve purchase orders',

        // Stock Requests / Release
        'create stock requests', 'approve stock release',

        // Stock Visibility / Admin Controls
        'view stock movements', 'view incoming deliveries', 'override stock',

        // Audit
        'view audit logs',

        // Users
        'view users', 'create users', 'edit users', 'delete users',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // Roles
    $admin = Role::firstOrCreate(['name' => 'admin']);
    $admin->givePermissionTo(Permission::all());

    $pharmacist = Role::firstOrCreate(['name' => 'pharmacist']);
    $pharmacist->givePermissionTo([
        'view products', 'view inventory',
        'view patients', 'create patients', 'edit patients',
        'view prescriptions', 'create prescriptions', 'edit prescriptions',
        'view sales', 'create sales',
        'create stock requests',
        'view reports',
    ]);

    $staff = Role::firstOrCreate(['name' => 'staff']);
    $staff->givePermissionTo([
        'view products', 'create products', 'edit products', 'delete products',
        'view inventory', 'create inventory', 'edit inventory', 'delete inventory',
        'view reports',
        'create purchase orders', 'view purchase orders',
        'approve stock release',
        'view stock movements', 'view incoming deliveries',
    ]);

    // Limit admin to governance and oversight actions.
    $admin->syncPermissions([
        'approve purchase orders',
        'view stock movements',
        'view incoming deliveries',
        'override stock',
        'view patients',
        'view audit logs',
        'view purchase orders',
        'view products',
        'view inventory',
        'view reports',
    ]);

}
}