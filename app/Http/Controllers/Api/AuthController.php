<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_token' => ['nullable', 'string'],
            'device_type' => ['nullable', 'in:android,ios'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        if (!$user->hasRole('security_officer')) {
            return response()->json(['message' => 'Mobile app is for security officers only.'], 403);
        }

        // If 2FA is enabled, require code
        if ($user->isTwoFactorEnabled()) {
            if (!$request->two_factor_code) {
                return response()->json([
                    'requires_2fa' => true,
                    'message' => 'Two-factor authentication code required.',
                ], 200);
            }

            $google2fa = new Google2FA();
            if (!$google2fa->verifyKey(decrypt($user->two_factor_secret), $request->two_factor_code)) {
                return response()->json(['message' => 'Invalid 2FA code.'], 401);
            }
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        // Save FCM token
        if ($request->device_token) {
            FcmToken::updateOrCreate(
                ['token' => $request->device_token],
                ['user_id' => $user->id, 'device_type' => $request->device_type]
            );
        }

        activity()->causedBy($user)->log('Mobile login');

        return response()->json([
            'token' => $token,
            'user' => $user->load('building'),
            'shift' => Shift::where('user_id', $user->id)
                ->where('status', 'active')
                ->with('building', 'relief')
                ->first(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->device_token) {
            FcmToken::where('token', $request->device_token)->delete();
        }
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('building'),
            'shift' => Shift::where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->with('building', 'relief')
                ->first(),
        ]);
    }
}
