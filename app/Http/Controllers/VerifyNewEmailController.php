<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class VerifyNewEmailController extends Controller
{
    public function verify(Request $request)
    {
        $user = User::findOrFail($request->id);

        if (! hash_equals((string) $request->token, (string) $user->email_verification_token)) {
            return Redirect::route('profile.edit')
                ->with('error', 'Invalid verification token.');
        }

        if (! $request->hasValidSignature()) {
            return Redirect::route('profile.edit')
                ->with('error', 'Verification link has expired.');
        }

        // Update the email
        $user->forceFill([
            'email' => $user->pending_email,
            'email_verified_at' => now(),
            'pending_email' => null,
            'email_verification_token' => null,
        ])->save();

        return Redirect::route('profile.edit')
            ->with('status', 'Email address has been verified and updated successfully.');
    }
}
