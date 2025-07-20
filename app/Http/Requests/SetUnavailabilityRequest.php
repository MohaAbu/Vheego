<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetUnavailabilityRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|in:reservation,maintenance,renter_blocked',
        ];
    }

    public function messages()
    {
        return [
            'start_date.after_or_equal' => 'The start date must be today or a future date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }
}