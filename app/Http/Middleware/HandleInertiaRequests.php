<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        try {
            $globalSettings = Setting::all_settings();
        } catch (\Exception $e) {
            $globalSettings = [];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $this->getUserWithRelations($request->user()) : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'info' => $request->session()->get('info'),
            ],
            'globalSettings' => $globalSettings,
            'appName' => $globalSettings['platform_name'] ?? config('app.name', 'Vheego'),
        ];
    }

    /**
     * Get user data with necessary relationships loaded
     */
    private function getUserWithRelations($user)
    {
        // Load agency relationship for renters
        if ($user->user_type === 'renter') {
            $user->load('agency');
        }
        
        return array_merge(
            $user->toArray(),
            ['profile_picture_url' => $user->profile_picture_url]
        );
    }
}
