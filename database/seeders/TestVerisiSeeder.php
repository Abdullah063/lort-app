<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\EntrepreneurProfile;
use App\Models\Company;
use App\Models\Goal;
use App\Models\Interest;
use App\Models\PackageDefinition;
use App\Models\Membership;
use App\Models\PhotoGallery;
use Illuminate\Support\Facades\Hash;

class TestVerisiSeeder extends Seeder
{
    public function run(): void
    {
        $userRole = Role::where('name', 'user')->first();
        $freePackage = PackageDefinition::where('name', 'free')->first();
        $silverPackage = PackageDefinition::where('name', 'silver')->first();

        $goalIds = Goal::pluck('id')->toArray();
        $interestIds = Interest::pluck('id')->toArray();

        // =============================================
        // KULLANICI 1 - Free paket
        // =============================================
        $user1 = User::create([
            'name'      => 'Ahmet',
            'surname'   => 'Yılmaz',
            'email'     => 'ahmet@test.com',
            'password'  => Hash::make('123456'),
            'phone'     => '05551111111',
            'is_active' => true,
        ]);

        $user1->roles()->attach($userRole->id, [
            'assigned_by' => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        Membership::create([
            'user_id'    => $user1->id,
            'package_id' => $freePackage->id,
            'starts_at'  => now(),
            'is_active'  => true,
        ]);

        EntrepreneurProfile::create([
            'user_id'    => $user1->id,
            'category'   => 'individual',
            'about_me'   => 'Teknoloji girişimcisi, mobil uygulama geliştirici',
            'is_online'  => true,
        ]);

        Company::create([
            'user_id'       => $user1->id,
            'business_name' => 'Yılmaz Teknoloji',
            'position'      => 'CEO & Founder',
            'sector'        => 'Teknoloji',
            'country'       => 'Türkiye',
            'city'          => 'İstanbul',
        ]);

        // Rastgele 2 hedef ve 2 ilgi alanı seç
        if (count($goalIds) >= 2) {
            $user1->goals()->attach(array_slice($goalIds, 0, 2));
        }
        if (count($interestIds) >= 2) {
            $user1->interests()->attach(array_slice($interestIds, 0, 2));
        }

        PhotoGallery::create([
            'user_id'    => $user1->id,
            'image_url'  => 'https://picsum.photos/seed/user1-1/400/400',
            'sort_order' => 1,
        ]);

        // =============================================
        // KULLANICI 2 - Silver paket
        // =============================================
        $user2 = User::create([
            'name'      => 'Ayşe',
            'surname'   => 'Demir',
            'email'     => 'ayse@test.com',
            'password'  => Hash::make('123456'),
            'phone'     => '05552222222',
            'is_active' => true,
        ]);

        $user2->roles()->attach($userRole->id, [
            'assigned_by' => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        Membership::create([
            'user_id'    => $user2->id,
            'package_id' => $silverPackage ? $silverPackage->id : $freePackage->id,
            'starts_at'  => now(),
            'is_active'  => true,
        ]);

        EntrepreneurProfile::create([
            'user_id'    => $user2->id,
            'category'   => 'corporate',
            'about_me'   => 'E-ticaret uzmanı, dijital pazarlama danışmanı',
            'is_online'  => false,
        ]);

        Company::create([
            'user_id'       => $user2->id,
            'business_name' => 'Demir Dijital',
            'position'      => 'Kurucu Ortak',
            'sector'        => 'Dijital Pazarlama',
            'country'       => 'Türkiye',
            'city'          => 'Ankara',
        ]);

        if (count($goalIds) >= 4) {
            $user2->goals()->attach(array_slice($goalIds, 1, 3));
        }
        if (count($interestIds) >= 4) {
            $user2->interests()->attach(array_slice($interestIds, 2, 3));
        }

        PhotoGallery::create([
            'user_id'    => $user2->id,
            'image_url'  => 'https://picsum.photos/seed/user2-1/400/400',
            'sort_order' => 1,
        ]);

        PhotoGallery::create([
            'user_id'    => $user2->id,
            'image_url'  => 'https://picsum.photos/seed/user2-2/400/400',
            'sort_order' => 2,
        ]);

        // =============================================
        // KULLANICI 3 - Free paket
        // =============================================
        $user3 = User::create([
            'name'      => 'Mehmet',
            'surname'   => 'Kaya',
            'email'     => 'mehmet@test.com',
            'password'  => Hash::make('123456'),
            'phone'     => '05553333333',
            'is_active' => true,
        ]);

        $user3->roles()->attach($userRole->id, [
            'assigned_by' => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        Membership::create([
            'user_id'    => $user3->id,
            'package_id' => $freePackage->id,
            'starts_at'  => now(),
            'is_active'  => true,
        ]);

        EntrepreneurProfile::create([
            'user_id'    => $user3->id,
            'category'   => 'individual',
            'about_me'   => 'Finans sektöründe 10 yıl deneyim, yatırım danışmanı',
            'is_online'  => true,
        ]);

        Company::create([
            'user_id'       => $user3->id,
            'business_name' => 'Kaya Yatırım',
            'position'      => 'Genel Müdür',
            'sector'        => 'Finans',
            'country'       => 'Türkiye',
            'city'          => 'İzmir',
        ]);

        if (count($goalIds) >= 3) {
            $user3->goals()->attach(array_slice($goalIds, 0, 3));
        }
        if (count($interestIds) >= 3) {
            $user3->interests()->attach(array_slice($interestIds, 0, 3));
        }

        PhotoGallery::create([
            'user_id'    => $user3->id,
            'image_url'  => 'https://picsum.photos/seed/user3-1/400/400',
            'sort_order' => 1,
        ]);

        $this->command->info('✅ 3 test kullanıcısı oluşturuldu:');
        $this->command->info('   ahmet@test.com  / 123456 (Free)');
        $this->command->info('   ayse@test.com   / 123456 (Silver)');
        $this->command->info('   mehmet@test.com / 123456 (Free)');
    }
}