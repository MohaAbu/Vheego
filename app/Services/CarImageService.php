<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class CarImageService
{
    /**
     * Handle multiple image uploads for a car
     */
    public function handleImageUpload(Car $car, array $images): void
    {
        $isFirstImage = $car->images()->count() === 0;

        foreach ($images as $index => $image) {
            if ($image instanceof UploadedFile) {
                $path = $image->store('car_images', 'public');
                
                CarImage::create([
                    'car_id' => $car->id,
                    'image_path' => $path,
                    'is_primary' => $isFirstImage && $index === 0
                ]);
            }
        }
    }

    /**
     * Remove specific images by their IDs
     */
    public function removeImages(Car $car, array $imageIds): void
    {
        $images = CarImage::where('car_id', $car->id)
                         ->whereIn('id', $imageIds)
                         ->get();

        foreach ($images as $image) {
            // Delete the file from storage
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete the database record
            $image->delete();
        }

        // If we deleted the primary image, set a new one
        $this->ensurePrimaryImageExists($car);
    }

    /**
     * Set a specific image as primary
     */
    public function setPrimaryImage(Car $car, int $imageId): void
    {
        // First, remove primary status from all images
        CarImage::where('car_id', $car->id)
               ->update(['is_primary' => false]);

        // Set the specified image as primary
        CarImage::where('car_id', $car->id)
               ->where('id', $imageId)
               ->update(['is_primary' => true]);
    }

    /**
     * Remove all images for a car (used when deleting a car)
     */
    public function removeAllImages(Car $car): void
    {
        $images = CarImage::where('car_id', $car->id)->get();

        foreach ($images as $image) {
            // Delete the file from storage
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete the database record
            $image->delete();
        }
    }

    /**
     * Ensure that at least one image is marked as primary
     */
    private function ensurePrimaryImageExists(Car $car): void
    {
        $hasPrimary = CarImage::where('car_id', $car->id)
                             ->where('is_primary', true)
                             ->exists();

        if (!$hasPrimary) {
            $firstImage = CarImage::where('car_id', $car->id)->first();
            if ($firstImage) {
                $firstImage->update(['is_primary' => true]);
            }
        }
    }

    /**
     * Get the primary image for a car
     */
    public function getPrimaryImage(Car $car): ?CarImage
    {
        return CarImage::where('car_id', $car->id)
                      ->where('is_primary', true)
                      ->first();
    }

    /**
     * Get all images for a car
     */
    public function getAllImages(Car $car): \Illuminate\Database\Eloquent\Collection
    {
        return CarImage::where('car_id', $car->id)
                      ->orderBy('is_primary', 'desc')
                      ->orderBy('created_at', 'asc')
                      ->get();
    }

    /**
     * Get the URL for a car image
     */
    public function getImageUrl(string $imagePath): string
    {
        return Storage::disk('public')->url($imagePath);
    }

    /**
     * Validate uploaded image files
     */
    public function validateImages(array $images): bool
    {
        foreach ($images as $image) {
            if (!$image instanceof UploadedFile) {
                return false;
            }

            // Check file size (max 5MB)
            if ($image->getSize() > 5 * 1024 * 1024) {
                return false;
            }

            // Check file type
            $allowedTypes = ['jpeg', 'jpg', 'png', 'webp'];
            if (!in_array(strtolower($image->getClientOriginalExtension()), $allowedTypes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get image count for a car
     */
    public function getImageCount(Car $car): int
    {
        return CarImage::where('car_id', $car->id)->count();
    }

    /**
     * Check if car has any images
     */
    public function hasImages(Car $car): bool
    {
        return $this->getImageCount($car) > 0;
    }
}