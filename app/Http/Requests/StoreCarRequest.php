<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'category' => 'required|in:economy,luxury,suv,sedan,hatchback,convertible',
            'license_plate' => 'required|string|max:50|unique:cars',
            'rental_price_per_day' => 'required|numeric|min:0',
            'fuel_type' => 'required|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'required|in:manual,automatic',
            'seats' => 'required|integer|min:1|max:20',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096|dimensions:min_width=300,min_height=200',
        ];
    }

    public function messages()
    {
        return [
            'images.*.dimensions' => 'Each image must be at least 300x200 pixels.',
            'images.*.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'images.max' => 'You can upload a maximum of 10 images.',
        ];
    }
}