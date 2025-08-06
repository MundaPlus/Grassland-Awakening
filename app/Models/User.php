<?php

namespace App\Models;

use App\Models\Presenters\UserPresenter;
use App\Models\Traits\HasHashedMediaTrait;
use App\Traits\HasObfuscatedId;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory;
    use HasHashedMediaTrait;
    use HasRoles;
    use HasObfuscatedId;
    use Notifiable;
    use SoftDeletes;
    use UserPresenter;

    protected $guarded = [
        'id',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'datetime',
            'last_login' => 'datetime',
            'deleted_at' => 'datetime',
            'social_profiles' => 'array',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Retrieve the providers associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function providers(): HasMany
    {
        return $this->hasMany(UserProvider::class);
    }

    public function player()
    {
        return $this->hasOne(Player::class);
    }

    /**
     * Check if the user is an administrator
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }
}
