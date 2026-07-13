<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;

class TwoFactorSetupController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        return Inertia::render('Auth/TwoFactorSetup', [
            'enabled' => $user->isTwoFactorEnabled(),
        ]);
    }

    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();
        $google2fa = new Google2FAQRCode();
        $secret = $google2fa->generateSecretKey();

        $user->two_factor_secret = encrypt($secret);
        $user->two_factor_confirmed_at = null;
        $user->save();

        $qrCodeUrl = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qr_code' => $qrCodeUrl,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);

        if (!$google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        activity()->causedBy($user)->log('Two-factor authentication enabled');

        return back()->with('success', 'Two-factor authentication enabled successfully.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required']]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $request->user()->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        activity()->causedBy($request->user())->log('Two-factor authentication disabled');

        return back()->with('success', 'Two-factor authentication disabled.');
    }
}
