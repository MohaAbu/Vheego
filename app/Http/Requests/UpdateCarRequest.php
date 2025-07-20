<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('features') && is_string($this->features)) {
            $this->merge([
                'features' => json_decode($this->features, true) ?? []
            ]);
        }

        if ($this->has('remove_image_ids') && is_string($this->remove_image_ids)) {
            $this->merge([
                'remove_image_ids' => json_decode($this->remove_image_ids, true) ?? []
            ]);
        }
    }

    public function rules()
    {
        $carId = $this->route('car');
        
        return [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'category' => 'required|in:economy,luxury,suv,sedan,hatchback,convertible',
            'license_plate' => 'required|string|max:50|unique:cars,license_plate,' . $carId,
            'rental_price_per_day' => 'required|numeric|min:0',
            'fuel_type' => 'required|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'required|in:manual,automatic',
            'seats' => 'required|integer|min:1|max:20',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096|dimensions:min_width=300,min_height=200',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer|exists:car_images,id',
            'primary_image_id' => 'nullable|integer|exists:car_images,id',
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