<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Car extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'make',
        'model',
        'year',
        'category',
        'license_plate',
        'rental_price_per_day',
        'fuel_type',
        'transmission',
        'seats',
        'features',
        'agency_id',
        'is_featured',
        'is_active',
        'delisted_reason',
        'delisted_by',
        'delisted_at',
        'special_offer',
    ];

    protected $casts = [
        'features' => 'array',
        'special_offer' => 'boolean',
    ];
    // Relationships
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function renter()
    {
        return $this->hasOneThrough(User::class, Agency::class, 'id', 'id', 'agency_id', 'renter_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function images()
    {
        return $this->hasMany(CarImage::class);
    }

    public function unavailabilities()
    {
        return $this->hasMany(CarUnavailability::class);
    }

    public function favorites()
    {
        return $this->hasMany(CustomerFavorite::class);
    }

    public function reviews()
    {
        return $this->hasManyThrough(
            \App\Models\Review::class,
            \App\Models\Reservation::class,
            'car_id', // Foreign key on reservations
            'reservation_id', // Foreign key on reviews
            'id', // Local key on cars
            'id' // Local key on reservations
        );
    }

    public function delistedBy()
    {
        return $this->belongsTo(User::class, 'delisted_by');
    }

    /**
     * Check if the car is available for the given date range.
     * Returns true if available, false if not.
     */
    public function isAvailable($startDate, $endDate)
    {
        // Check for overlapping reservations (active or upcoming)
        $hasReservation = $this->reservations()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
                });
            })
            ->exists();

        if ($hasReservation) {
            return false;
        }

        // Check for overlapping unavailability
        $hasUnavailability = $this->unavailabilities()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })
            ->exists();

        return !$hasUnavailability;
    }
}
