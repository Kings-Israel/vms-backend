<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;

class LoginController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect.']);
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function showTwoFactorChallenge(Request $request): Response|RedirectResponse
    {
        if (!$request->session()->has('auth.2fa_user_id')) {
            return redirect()->route('login');
        }
        return Inertia::render('Auth/TwoFactor');
    }

    public function twoFactorChallenge(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get('auth.2fa_user_id');
        $user = User::findOrFail($userId);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'The provided two-factor authentication code is invalid.']);
        }

        $remember = $request->session()->pull('auth.remember', false);
        $request->session()->forget('auth.2fa_user_id');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        activity()->causedBy($user)->log('Two-factor authentication completed');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
