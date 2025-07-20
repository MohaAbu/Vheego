<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'car_id',
        'start_date',
        'end_date',
        'total_price',
        'status',
    ];
    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
