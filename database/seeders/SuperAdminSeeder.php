<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super admin kullanıcı oluştur
        $superAdmin = User::create([
            'name'           => 'Super',
            'surname'        => 'Admin',
            'email'          => 'admin@test.com',
            'password'       => Hash::make('123456'),  
            'phone'          => null,
            'email_verified' => true,
            'phone_verified' => true,
            'is_active'      => true,
        ]);

        // Super admin rolünü ata
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdmin->roles()->attach($superAdminRole->id, [
            'assigned_by' => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
