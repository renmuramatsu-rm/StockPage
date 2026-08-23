<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Google sign-in. This app has a single owner rather than open
 * registration, so any Google account is allowed to complete the OAuth
 * dance, but only the address configured in LOGIN_EMAIL is actually let
 * in — anyone else's Google login is rejected after the callback.
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
            return redirect()->route('login')->withErrors(['email' => 'Googleログインに失敗しました。もう一度お試しください。']);
        }

        $allowedEmail = config('services.login.allowed_email');

        if (! $allowedEmail || strcasecmp($googleUser->getEmail(), $allowedEmail) !== 0) {
            return redirect()->route('login')->withErrors(['email' => 'このGoogleアカウントではログインできません。']);
        }

        $user = User::where('email', $allowedEmail)->first();

        if ($user) {
            $user->update([
                'name' => $googleUser->getName() ?: $user->name,
                'google_id' => $googleUser->getId(),
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: 'Owner',
                'email' => $allowedEmail,
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(40)),
            ]);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('themes.dashboard'));
    }
}
