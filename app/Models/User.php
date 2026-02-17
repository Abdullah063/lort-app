<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;



class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'name',
        'surname',
        'phone',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified' => 'boolean',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    // =============================================
    // 1:1 İLİŞKİLER (hasOne)
    // =============================================

    // Kullanıcının 1 girişimci profili var
    public function entrepreneurProfile()
    {
        return $this->hasOne(EntrepreneurProfile::class);
    }

    // Kullanıcının 1 şirketi var
    public function company()
    {
        return $this->hasOne(Company::class);
    }

    // =============================================
    // 1:N İLİŞKİLER (hasMany)
    // =============================================

    // Kullanıcının birden fazla fotoğrafı olabilir
    public function photoGallery()
    {
        return $this->hasMany(PhotoGallery::class);
    }

    // Kullanıcının birden fazla ilanı olabilir
    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    // Kullanıcının birden fazla üyeliği olabilir (geçmiş + aktif)
    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    // Kullanıcının bildirimleri
    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    // Kullanıcının refresh token'ları
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    // Kullanıcının favorileri
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // Kullanıcının yaptığı kaydırmalar
    public function swipes()
    {
        return $this->hasMany(Swipe::class, 'swiper_id');
    }

    // =============================================
    // N:N İLİŞKİLER (belongsToMany)
    // =============================================

    // Kullanıcının rolleri (user_roles ara tablosu üzerinden)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('assigned_by', 'created_at');
    }

    // Kullanıcının hedefleri (user_goals ara tablosu üzerinden)
    public function goals()
    {
        return $this->belongsToMany(Goal::class, 'user_goals');
    }

    // Kullanıcının ilgi alanları (user_interests ara tablosu üzerinden)
    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interests');
    }

    // =============================================
    // YARDIMCI METODLAR
    // =============================================

    // Kullanıcının aktif üyeliğini getir
    public function activeMembership()
    {
        return $this->hasOne(Membership::class)->where('is_active', true);
    }

    // Kullanıcının belirli bir rolü var mı?
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    // Kullanıcı admin mi?
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super_admin');
    }

    // Kullanıcı super admin mi?
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}
