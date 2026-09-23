<?php

declare(strict_types=1);

namespace App\Services\Otp;

use App\Contracts\Integrations\OtpServiceInterface;
use App\Exceptions\Api\ApiException;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;

/**
 * Local OTP lifecycle (BRD §6.1 / §10):
 *  - 6-digit code, valid 5 minutes;
 *  - resend cooldown 60 seconds;
 *  - 3 consecutive failures -> 10 minutes lock;
 *  - next 3 consecutive failures -> 1 hour lock.
 *
 * One phone number = one account. A user is created on first successful
 * verification (automatic registration).
 */
class OtpService
{
    public function __construct(
        private readonly OtpServiceInterface $otpDeliverer,
    ) {}

    public function send(string $phone): array
    {
        $phone = $this->normalizePhone($phone);

        $latest = OtpCode::query()
            ->where('phone_number', $phone)
            ->orderByDesc('created_at')
            ->first();

        if ($latest !== null) {
            if ($latest->blocked_until !== null && $latest->blocked_until->isFuture()) {
                throw new ApiException(
                    __('auth.otp_blocked', ['seconds' => $latest->blocked_until->diffInSeconds(now())]),
                    429
                );
            }

            $cooldownLeft = $latest->last_sent_at->diffInSeconds(now());
            if ($cooldownLeft < $this->cooldownSeconds()) {
                throw new ApiException(
                    __('auth.otp_cooldown', ['seconds' => $this->cooldownSeconds() - $cooldownLeft]),
                    429
                );
            }
        }

        $code = $this->otpDeliverer->generateCode();

        $record = new OtpCode;
        $record->phone_number = $phone;
        $record->code_hash = Hash::make($code);
        $record->expires_at = now()->addMinutes($this->ttlMinutes());
        $record->last_sent_at = now();
        $record->save();

        $this->otpDeliverer->deliver($phone, $code);

        $debugCode = null;
        if (
            config('services.otp.driver') === 'fake'
            && (bool) config('services.otp.fake.expose_code')
            && ! app()->isProduction()
        ) {
            $debugCode = $code;
        }

        return [
            'phone_number' => $phone,
            'ttl_seconds' => $this->ttlMinutes() * 60,
            'resend_after_seconds' => $this->cooldownSeconds(),
            'debug_code' => $debugCode,
        ];
    }

    public function verify(string $phone, string $code): OtpCode
    {
        $phone = $this->normalizePhone($phone);

        $latest = OtpCode::query()
            ->where('phone_number', $phone)
            ->orderByDesc('created_at')
            ->first();

        if ($latest === null || $latest->expires_at->isPast()) {
            throw new ApiException(__('auth.otp_expired'), 422);
        }

        if ($latest->verified_at !== null) {
            throw new ApiException(__('auth.otp_already_used'), 422);
        }

        if ($latest->blocked_until !== null && $latest->blocked_until->isFuture()) {
            throw new ApiException(
                __('auth.otp_blocked', ['seconds' => $latest->blocked_until->diffInSeconds(now())]),
                429
            );
        }

        if (! Hash::check($code, $latest->code_hash)) {
            $fails = $latest->consecutive_failures + 1;
            $latest->consecutive_failures = $fails;
            $latest->attempts++;

            if ($fails > 0 && $fails % 3 === 0) {
                $minutes = $fails >= 6 ? $this->lockMinutesEscalated() : $this->lockMinutes();
                $latest->blocked_until = now()->addMinutes($minutes);
                $latest->save();

                throw new ApiException(
                    __('auth.otp_blocked', ['seconds' => $minutes * 60]),
                    429
                );
            }

            $latest->save();

            throw new ApiException(__('auth.otp_invalid'), 422);
        }

        $latest->attempts++;
        $latest->verified_at = now();
        $latest->save();

        // Reset failure counter for the phone on success.
        OtpCode::query()
            ->where('phone_number', $phone)
            ->update(['consecutive_failures' => 0]);

        return $latest;
    }

    private function ttlMinutes(): int
    {
        return (int) config('services.otp.ttl_minutes', 5);
    }

    private function cooldownSeconds(): int
    {
        return (int) config('services.otp.resend_cooldown_seconds', 60);
    }

    private function lockMinutes(): int
    {
        return (int) config('services.otp.lock_minutes', 10);
    }

    private function lockMinutesEscalated(): int
    {
        return (int) config('services.otp.lock_minutes_escalated', 60);
    }

    /**
     * Normalize an Algerian number to E.164 (+213XXXXXXXXX).
     * Accepts "+213XXXXXXXXX" or "0XXXXXXXXX".
     */
    public function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        $phone = preg_replace('/\s+/', '', $phone);

        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            $phone = '+213'.substr($phone, 1);
        }

        if (! preg_match('/^\+213[0-9]{9}$/', $phone)) {
            throw new ApiException(__('validation.phone', ['attribute' => 'phone_number']), 422);
        }

        return $phone;
    }
}
