<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Events\PendingEmailChange;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'profile_picture_url' => $user->profile_picture_url,
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        
        // Handle email change separately
        $newEmail = $data['email'] ?? null;
        $emailChanged = $newEmail && $newEmail !== $user->email;
        
        if ($emailChanged) {
            // Store the new email in a pending state
            $user->pending_email = $newEmail;
            // Generate verification token
            $token = sha1(uniqid(rand(), true));
            $user->email_verification_token = $token;
            
            // Send verification email to the new address
            event(new PendingEmailChange($user, $newEmail));
            
            // Remove email from validated data to prevent immediate update
            unset($data['email']);
        }
        
        $user->fill($data);
        
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_pictures', $filename, 'public');
            $user->profile_picture = $filename; 
        }

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete profile picture if exists
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}