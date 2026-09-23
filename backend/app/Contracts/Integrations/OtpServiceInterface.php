<?php

declare(strict_types=1);

namespace App\Contracts\Integrations;

/**
 * Contract for delivering one-time passwords.
 *
 * Production implementations: Firebase Auth, WhatsApp OTP gateway.
 * During development/tests: FakeOtpService.
 */
interface OtpServiceInterface
{
    public function generateCode(): string;

    public function deliver(string $phoneNumber, string $code): void;
}