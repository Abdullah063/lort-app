<?php

namespace Database\Seeders;

use App\Models\EntrepreneurProfile;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Goal;
use App\Models\Company;
use App\Models\Interest;

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

        $profile = EntrepreneurProfile::create([
            'user_id'            => $superAdmin->id,
            'category_id'        => 1,
            'preferred_language'  => 'tr',
            'about_me'           => 'hayalini kurduğumzuz hayatı başkaları yaşıyor xd.',
            'profile_image_url'  => 'abdulahaltun.com.te/prifile',
            'is_online'          => true,
            'birth_date'         => '1990-01-01',

        ]);

        $goalIds = Goal::whereIn('id', [1, 2])->pluck('id');
        $superAdmin->goals()->attach($goalIds);

        $interestIds = Interest::whereIn('id', [1, 2])->pluck('id');
        $superAdmin->interests()->attach($interestIds);

        $company = Company::create([
            'user_id'       => $superAdmin->id,
            'business_name' => 'abdullah holding',
            'sector'        => 'yazılım',
            'country'       => 'Turkey',
            'city'          => 'Gaziantep',
            'address'       => 'Gaziantep / otogar '
        ]);
    }
}
