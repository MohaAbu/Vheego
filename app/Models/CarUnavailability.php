<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarUnavailability extends Model
{
    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
