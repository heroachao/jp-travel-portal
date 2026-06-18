<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'admin.access',
            'articles.create',
            'articles.edit',
            'articles.review',
            'articles.publish',
            'seo.manage',
            'ads.manage',
            'settings.manage',
            'users.manage',
            'system.logs',
        ];

        $permissionModels = collect($permissions)
            ->mapWithKeys(fn (string $permission) => [
                $permission => Permission::findOrCreate($permission, 'web'),
            ]);

        $roles = [
            'super-admin' => $permissions,
            'chief-editor' => ['admin.access', 'articles.create', 'articles.edit', 'articles.review', 'articles.publish', 'seo.manage'],
            'editor' => ['admin.access', 'articles.create', 'articles.edit'],
            'seo-operator' => ['admin.access', 'seo.manage', 'articles.edit'],
            'ad-operator' => ['admin.access', 'ads.manage'],
        ];

        foreach ($roles as $name => $rolePermissions) {
            Role::findOrCreate($name, 'web')
                ->syncPermissions($permissionModels->only($rolePermissions)->values()->all());
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => '超级管理员', 'password' => Hash::make('ChangeMe123!')]
        );

        $admin->assignRole('super-admin');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
