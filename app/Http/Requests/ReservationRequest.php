<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Car;

class ReservationRequest extends FormRequest
{
    public function authorize()
    {
        // Only authenticated users can make reservations
        return $this->user() !== null && $this->user()->user_type === 'customer';
    }

    public function rules()
    {
        return [
            'car_id' => ['required', 'exists:cars,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $carId = $this->input('car_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            if ($carId && $startDate && $endDate) {
                $car = Car::find($carId);
                if (!$car || !$car->isAvailable($startDate, $endDate)) {
                    $validator->errors()->add('car_id', 'The selected car is not available for the chosen dates.');
                }
            }
        });
    }
} 