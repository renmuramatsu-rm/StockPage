<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Google sign-in for the Next.js SPA. The app allows open self-registration,
 * so any Google account may sign in — an existing user is matched by
 * google_id/email, otherwise a new account is created on the fly, same as
 * email/password registration. Laravel only handles the OAuth redirect
 * dance server-side; the resulting session cookie is what the SPA relies
 * on, so every outcome below sends the browser back to the frontend.
 */
class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect(config('app.frontend_url').'/login?error=google_failed');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $user->update([
                'name' => $googleUser->getName() ?: $user->name,
                'google_id' => $googleUser->getId(),
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getEmail(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(40)),
            ]);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect(config('app.frontend_url').'/login/callback');
    }
}
