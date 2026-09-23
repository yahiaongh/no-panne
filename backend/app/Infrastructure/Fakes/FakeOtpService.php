<?php

declare(strict_types=1);

namespace App\Infrastructure\Fakes;

use App\Contracts\Integrations\OtpServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Deterministic OTP provider for local development and automated tests.
 *
 * Generates either the configured fixed code (OTP_FAKE_CODE) or a random code
 * when none is set. "Delivery" only writes to the log — no external network.
 */
class FakeOtpService implements OtpServiceInterface
{
    public function generateCode(): string
    {
        $fixed = config('services.otp.fake.code', '123456');

        if (is_string($fixed) && $fixed !== '' && ! app()->isProduction()) {
            return $fixed;
        }

        return (string) random_int(100000, 999999);
    }

    public function deliver(string $phoneNumber, string $code): void
    {
        if ((bool) config('services.otp.fake.expose_code', app()->environment('local', 'testing'))) {
            Log::info("[FakeOtp] code {$code} for {$phoneNumber}");
        }
    }
}