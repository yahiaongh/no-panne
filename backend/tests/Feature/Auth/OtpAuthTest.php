<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpAuthTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '+213661234567';

    public function test_send_otp_returns_success_envelope_with_debug_code(): void
    {
        $response = $this->postJson('/api/v1/auth/send-otp', ['phone_number' => self::PHONE]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.phone_number', '+213661234567')
            ->assertJsonPath('data.ttl_seconds', 300)
            ->assertJsonStructure(['data' => ['phone_number', 'ttl_seconds', 'resend_after_seconds', 'debug_code']]);
    }

    public function test_resend_within_cooldown_is_rejected(): void
    {
        $this->postJson('/api/v1/auth/send-otp', ['phone_number' => self::PHONE])->assertOk();

        $response = $this->postJson('/api/v1/auth/send-otp', ['phone_number' => self::PHONE]);

        $response->assertStatus(429)
            ->assertJsonPath('success', false);
        $this->assertDatabaseCount('otp_codes', 1);
    }

    public function test_send_otp_rejects_invalid_phone(): void
    {
        $response = $this->postJson('/api/v1/auth/send-otp', ['phone_number' => '1234']);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'validation_error');
    }

    public function test_verify_otp_registers_user_and_returns_token(): void
    {
        $send = $this->postJson('/api/v1/auth/send-otp', ['phone_number' => self::PHONE]);
        $code = $send->json('data.debug_code');

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'phone_number' => self::PHONE,
            'code' => $code,
            'device_token' => 'fcm-device-1',
            'device_platform' => 'android',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.role_type', 'client');

        $this->assertDatabaseHas('users', ['phone_number' => '+213661234567']);
        $this->assertDatabaseHas('device_tokens', ['token' => 'fcm-device-1']);
    }

    public function test_verify_otp_with_used_code_fails(): void
    {
        $send = $this->postJson('/api/v1/auth/send-otp', ['phone_number' => self::PHONE]);
        $code = $send->json('data.debug_code');

        $this->postJson('/api/v1/auth/verify-otp', ['phone_number' => self::PHONE, 'code' => $code])->assertOk();
        $this->postJson('/api/v1/auth/verify-otp', ['phone_number' => self::PHONE, 'code' => $code])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Ce code a déjà été utilisé. Veuillez demander un nouveau code.');
    }

    public function test_three_consecutive_failures_block_for_ten_minutes(): void
    {
        $send = $this->postJson('/api/v1/auth/send-otp', ['phone_number' => self::PHONE]);
        $code = $send->json('data.debug_code');

        $attempts = 2;
        for ($i = 0; $i < $attempts; $i++) {
            $bad = substr($code, 0, 5).($code[5] === '0' ? '1' : '0');
            $r = $this->postJson('/api/v1/auth/verify-otp', ['phone_number' => self::PHONE, 'code' => $bad]);
            $r->assertStatus(422);
        }

        // Third consecutive failure triggers the 10-minute lock.
        $bad = substr($code, 0, 5).($code[5] === '0' ? '1' : '0');
        $blocked = $this->postJson('/api/v1/auth/verify-otp', ['phone_number' => self::PHONE, 'code' => $bad]);
        $blocked->assertStatus(429)->assertJsonPath('success', false);

        $response = $this->postJson('/api/v1/auth/verify-otp', ['phone_number' => self::PHONE, 'code' => $code]);

        $response->assertStatus(429)
            ->assertJsonPath('success', false);
    }

    public function test_login_after_registration_returns_same_user(): void
    {
        $send = $this->postJson('/api/v1/auth/send-otp', ['phone_number' => self::PHONE]);
        $this->postJson('/api/v1/auth/verify-otp', ['phone_number' => self::PHONE, 'code' => $send->json('data.debug_code')])->assertOk();

        $this->travel(61)->seconds();

        $second = $this->postJson('/api/v1/auth/send-otp', ['phone_number' => self::PHONE]);
        $response = $this->postJson('/api/v1/auth/verify-otp', ['phone_number' => self::PHONE, 'code' => $second->json('data.debug_code')]);

        $response->assertOk();
        $this->assertCount(1, User::where('phone_number', '+213661234567')->get());
    }
}