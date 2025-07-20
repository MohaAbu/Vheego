<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Setting;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share settings with all views
        try {
            $settings = Setting::all_settings();
            View::share('globalSettings', $settings);
            
            // Set app name dynamically if it exists in settings
            if (isset($settings['platform_name'])) {
                config(['app.name' => $settings['platform_name']]);
            }
            
        } catch (\Exception $e) {
            // Handle case where settings table doesn't exist yet (during migration)
            View::share('globalSettings', []);
        }
    }
}
