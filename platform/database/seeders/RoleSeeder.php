<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'admin.access',
            'articles.create',
            'articles.edit',
            'articles.review',
            'articles.publish',
            'seo.manage',
            'ads.manage',
            'users.manage',
            'system.logs',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $roles = [
            'super-admin' => $permissions,
            'chief-editor' => ['admin.access', 'articles.create', 'articles.edit', 'articles.review', 'articles.publish', 'seo.manage'],
            'editor' => ['admin.access', 'articles.create', 'articles.edit'],
            'seo-operator' => ['admin.access', 'seo.manage', 'articles.edit'],
            'ad-operator' => ['admin.access', 'ads.manage'],
        ];

        foreach ($roles as $name => $rolePermissions) {
            Role::findOrCreate($name)->syncPermissions($rolePermissions);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => '超级管理员', 'password' => Hash::make('ChangeMe123!')]
        );

        $admin->assignRole('super-admin');
    }
}
