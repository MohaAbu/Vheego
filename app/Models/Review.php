<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'car_id',
        'customer_id',
        'agency_id',
        'rating',
        'comment',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function car()
    {
        // For direct car reviews, use direct relationship
        if ($this->car_id) {
            return $this->belongsTo(Car::class, 'car_id');
        }
        
        // For reservation-based reviews, use the through relationship
        return $this->hasOneThrough(
            \App\Models\Car::class,
            \App\Models\Reservation::class,
            'id', // Reservation's primary key
            'id', // Car's primary key
            'reservation_id', // Foreign key on reviews
            'car_id' // Foreign key on reservations
        );
    }

    // Direct car relationship for new review system
    public function carDirect()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
}
