<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\SendOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Models\DeviceToken;
use App\Models\User;
use App\Services\Otp\OtpService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpAuthController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    /**
     * POST /api/v1/auth/send-otp
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->send($request->input('phone_number'));

        return ApiResponse::success($result, __('auth.otp_sent'));
    }

    /**
     * POST /api/v1/auth/verify-otp
     *
     * Registers the account on first successful verification (one phone =
     * one account, BRD §6.1) then issues a Sanctum bearer token.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $code = $this->otpService->verify($request->input('phone_number'), $request->input('code'));
        $phone = $this->otpService->normalizePhone($request->input('phone_number'));

        $user = User::query()->withTrashed()->firstOrNew(['phone_number' => $phone]);

        if ($user->trashed()) {
            return ApiResponse::error(__('auth.account_deleted'), 403, 'account_deleted');
        }

        if ($user->exists === false) {
            $user->role_type = UserRole::Client;
            $user->country_code = '+213';
            $user->phone_verified_at = now();
            $user->save();
        }

        $user->forceFill(['phone_verified_at' => now(), 'last_login_at' => now()])->save();

        if (! $user->isActive()) {
            return ApiResponse::error(
                match ($user?->client?->status ?? $user?->provider?->status) {
                    AccountStatus::Suspended => __('auth.account_suspended'),
                    AccountStatus::Banned => __('auth.account_banned'),
                    default => __('auth.account_suspended'),
                },
                403,
                'account_inactive'
            );
        }

        if ($token = $request->input('device_token')) {
            DeviceToken::query()->updateOrCreate(
                ['user_id' => $user->id, 'token' => $token],
                [
                    'platform' => $request->input('device_platform', 'android'),
                    'last_seen_at' => now(),
                ]
            );
        }

        $abilities = match ($user->role_type) {
            UserRole::Provider => ['provider'],
            UserRole::Admin => ['admin'],
            default => ['client'],
        };

        $token = $user->createToken('mobile-'.$user->id, $abilities)->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->serializeUser($user),
        ], 'Connexion réussie.');
    }

    /**
     * POST /api/v1/auth/refresh-token
     *
     * Rotates the current bearer token (revoke + issue new).
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->user()->currentAccessToken();

        if ($token !== null) {
            $abilities = $token->abilities;
            $token->delete();
            $fresh = $user->createToken('mobile-'.$user->id, $abilities)->plainTextToken;
        } else {
            $fresh = $user->createToken('mobile-'.$user->id)->plainTextToken;
        }

        return ApiResponse::success([
            'token' => $fresh,
            'token_type' => 'Bearer',
        ], __('auth.token_refreshed'));
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'phone_number' => $user->phone_number,
            'role_type' => $user->role_type->value,
            'phone_verified' => $user->phone_verified_at !== null,
            'profile_completed' => $user->isClient()
                ? ($user->client?->first_name !== null)
                : ($user->provider !== null),
        ];
    }
}
