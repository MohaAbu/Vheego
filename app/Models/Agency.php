<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agency extends Model
{
    use HasFactory;
    protected $fillable = [
        'renter_id',
        'name',
        'contact_email',
        'contact_phone',
        'address',
        'description',
        'rating',
        'total_reviews',
        'verification_status',
        'admin_feedback',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'logo', // add logo to fillable
    ];
    // Relationships
    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Accessor for logo URL
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/agency_logos/' . $this->logo);
        }
        return asset('images/agency-placeholder.svg');
    }
}
