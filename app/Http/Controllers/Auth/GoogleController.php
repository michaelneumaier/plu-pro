<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth provider
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists with this Google ID
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // Update existing Google user info
                $user->update([
                    'google_avatar' => $googleUser->avatar,
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                ]);
            } else {
                // Check if a user exists with this email
                $existingUser = User::where('email', $googleUser->email)->first();

                if ($existingUser) {
                    // Link Google account to existing user
                    $existingUser->update([
                        'google_id' => $googleUser->id,
                        'google_avatar' => $googleUser->avatar,
                        'google_created_at' => now(),
                    ]);
                    $user = $existingUser;
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'google_avatar' => $googleUser->avatar,
                        'google_created_at' => now(),
                        'email_verified_at' => now(),
                        'password' => Hash::make(Str::random(24)), // Random password since they'll use Google OAuth
                        'role' => 'user', // Default role
                    ]);

                    // Assign default user role
                    $user->assignRole('user');
                }
            }

            // Log the user in
            Auth::login($user);

            // Redirect to intended page or dashboard
            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Google OAuth Error: ' . $e->getMessage());

            return redirect()->route('login')->with('error', 'Failed to authenticate with Google. Please try again.');
        }
    }

    /**
     * Unlink Google account from user
     */
    public function unlink(Request $request)
    {
        $user = $request->user();

        // Check if user has a password set (can still login without Google)
        if (!$user->password) {
            return back()->with('error', 'You must set a password before unlinking your Google account.');
        }

        $user->update([
            'google_id' => null,
            'google_avatar' => null,
            'google_created_at' => null,
        ]);

        return back()->with('status', 'Google account has been unlinked successfully.');
    }
}