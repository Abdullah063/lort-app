<?php

// =============================================
// 1. RolVeIzinSeeder (database/seeders/RolVeIzinSeeder.php)
// php artisan make:seeder RolVeIzinSeeder
// =============================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolVeIzinSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Roller ----
        $user = Role::create([
            'name' => 'user',
            'description' => 'Standart kullanıcı',
        ]);

        $admin = Role::create([
            'name' => 'admin',
            'description' => 'Yönetici - kullanıcı ve içerik yönetimi',
        ]);

        $superAdmin = Role::create([
            'name' => 'super_admin',
            'description' => 'Süper yönetici - tam yetki',
        ]);

        // ---- İzinler ----
        $modules = [
            'users',
            'roles',
            'permissions',
            'listings',
            'payments',
            'memberships',
            'refunds',
            'packages',
            'notifications',
            'languages',
            'translations',
        ];

        $actions = ['create', 'read', 'update', 'delete'];

        $allPermissions = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $allPermissions[] = Permission::create([
                    'module' => $module,
                    'action' => $action,
                    'description' => "$module - $action",
                ]);
            }
        }

        // ---- Rol-İzin Eşleştirmeleri ----

        // Super Admin → TÜM izinler
        $superAdmin->permissions()->attach(
            collect($allPermissions)->pluck('id')
        );

        // Admin → users, listings, payments, refunds, notifications okuma ve güncelleme
        $adminModules = ['users', 'listings', 'payments', 'refunds', 'notifications'];
        $adminActions = ['read', 'update'];

        $adminPermissions = collect($allPermissions)->filter(function ($p) use ($adminModules, $adminActions) {
            return in_array($p->module, $adminModules) && in_array($p->action, $adminActions);
        });

        $admin->permissions()->attach($adminPermissions->pluck('id'));

        // User → kendi profilini okuma (izin kontrolü backend'de yapılacak)
        // Kullanıcıya özel izin atamaya gerek yok, middleware'de rol kontrolü yeterli
    }
}