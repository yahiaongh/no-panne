<?php

namespace App\Providers;

use App\Contracts\Integrations\OtpServiceInterface;
use App\Infrastructure\Fakes\FakeOtpService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->bindIntegrationContracts();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimits();
    }

    /**
     * Bind integration contracts to their configured implementation.
     */
    protected function bindIntegrationContracts(): void
    {
        $this->app->bind(OtpServiceInterface::class, function ($app) {
            $driver = config('services.otp.driver', 'fake');

            return match ($driver) {
                default => $app->make(FakeOtpService::class),
            };
        });
    }

    /**
     * Named API rate limiters.
     */
    protected function configureRateLimits(): void
    {
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())
                ->response(fn () => \App\Support\ApiResponse::error('Trop de tentatives. Réessayez dans une minute.', 429, 'too_many_requests'));
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}