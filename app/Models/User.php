<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'profile_picture',
        'is_banned',
        'banned_reason',
        'banned_by',
        'banned_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['profile_picture_url'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    // Relationships
    public function agency()
    {
        return $this->hasOne(Agency::class, 'renter_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'customer_id');
    }

    public function carUnavailabilities()
    {
        return $this->hasMany(CarUnavailability::class, 'created_by');
    }

    public function delistedCars()
    {
        return $this->hasMany(Car::class, 'delisted_by');
    }

    public function bannedUsers()
    {
        return $this->hasMany(User::class, 'banned_by');
    }

    // Relationship: Customer favorites
    public function favorites()
    {
        return $this->hasMany(\App\Models\CustomerFavorite::class, 'customer_id');
    }

    // Accessor for profile picture URL
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/profile_pictures/' . $this->profile_picture);
        }
        return asset('images/user-placeholder.svg');
    }
}
