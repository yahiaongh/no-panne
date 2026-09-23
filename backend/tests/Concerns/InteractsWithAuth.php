<?php

namespace Tests\Concerns;

use App\Models\User;

trait InteractsWithAuth
{
    /**
     * Laravel's request guard caches the resolved user inside the container-scoped
     * guard instance. Within a single test method that issues several HTTP requests,
     * the FIRST authenticated user would otherwise stick for every subsequent request,
     * regardless of the Bearer token sent. Forgetting the guards forces re-resolution
     * from the actual token on each request (this is never needed in production, where
     * every HTTP request builds a fresh guard).
     */
    public function withToken(string $token, string $type = 'Bearer'): static
    {
        $this->withHeader('Authorization', $type.' '.(string) $token);
        $this->app['auth']->forgetGuards();

        return $this;
    }

    /**
     * Registers (or logs in) a phone via the real OTP endpoints.
     */
    protected function registerViaOtp(string $phone = '+213661234560'): array
    {
        $send = $this->postJson('/api/v1/auth/send-otp', ['phone_number' => $phone])->assertOk();
        $code = $send->json('data.debug_code');

        $response = $this->postJson('/api/v1/auth/verify-otp', ['phone_number' => $phone, 'code' => $code])->assertOk();

        $user = User::where('phone_number', $phone)->firstOrFail();

        return [
            'user' => $user,
            'token' => $response->json('data.token'),
        ];
    }
}
