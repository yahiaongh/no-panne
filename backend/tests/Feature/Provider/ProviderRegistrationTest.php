<?php

namespace Tests\Feature\Provider;

use App\Enums\AccountStatus;
use App\Models\Commune;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Wilaya;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAuth;
use Tests\TestCase;

class ProviderRegistrationTest extends TestCase
{
    use InteractsWithAuth, RefreshDatabase;

    public function test_provider_registration_creates_pending_profile_and_vehicle(): void
    {
        $wilaya = Wilaya::create(['code' => '31', 'name_fr' => 'Oran', 'active' => true]);
        $commune = Commune::create(['wilaya_id' => $wilaya->id, 'code' => '31001', 'name_fr' => 'Oran']);
        $services = Service::create(['code' => 'SRV-P1', 'name_fr' => 'Remorquage test', 'type' => 'urgence', 'active' => true]);

        $auth = $this->registerViaOtp('+213662000333');

        $response = $this->withToken($auth['token'])->postJson('/api/v1/provider/register', [
            'full_name' => 'Karim HADJ',
            'birth_date' => '1990-04-12',
            'wilaya_id' => $wilaya->id,
            'commune_id' => $commune->id,
            'coverage_radius_km' => 20,
            'service_ids' => [$services->id],
            'coverage_wilaya_ids' => [$wilaya->id],
            'current_lat' => 35.6971,
            'current_lng' => -0.6308,
            'vehicle' => [
                'vehicle_type' => 'camionnette',
                'brand' => 'Hyundai',
                'model' => 'H-1',
                'plate' => '312-44-B-31',
                'year' => 2019,
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'en_attente');

        $provider = Provider::where('user_id', $auth['user']->id)->firstOrFail();
        $this->assertSame(AccountStatus::Pending, $provider->status);
        $this->assertTrue($auth['user']->refresh()->isProvider());
        $this->assertTrue($provider->services()->where('service_id', $services->id)->exists());
        $this->assertTrue($provider->vehicles()->where('is_active', true)->exists());

        // Cannot register twice.
        $this->withToken($auth['token'])->postJson('/api/v1/provider/register', [
            'full_name' => 'Karim HADJ',
            'birth_date' => '1990-04-12',
            'wilaya_id' => $wilaya->id,
            'commune_id' => $commune->id,
            'coverage_radius_km' => 10,
            'service_ids' => [$services->id],
            'vehicle' => ['vehicle_type' => 'voiture', 'brand' => 'X', 'model' => 'Y', 'plate' => '999-99-9-31'],
        ])->assertStatus(409);
    }

    public function test_pending_provider_cannot_toggle_availability(): void
    {
        $auth = $this->registerViaOtp('+213662000444');
        $wilaya = Wilaya::create(['code' => '16', 'name_fr' => 'Alger', 'active' => true]);
        $commune = Commune::create(['wilaya_id' => $wilaya->id, 'code' => '16001', 'name_fr' => 'Alger Centre']);
        $service = Service::create(['code' => 'SRV-P2', 'name_fr' => 'Lavage test', 'type' => 'planifie', 'active' => true]);

        $this->withToken($auth['token'])->postJson('/api/v1/provider/register', [
            'full_name' => 'Samir',
            'birth_date' => '1985-09-01',
            'wilaya_id' => $wilaya->id,
            'commune_id' => $commune->id,
            'coverage_radius_km' => 15,
            'service_ids' => [$service->id],
            'vehicle' => ['vehicle_type' => 'moto', 'brand' => 'Yamaha', 'model' => 'MT', 'plate' => '160-12-3-16', 'year' => 2020],
        ])->assertCreated();

        $this->withToken($auth['token'])->putJson('/api/v1/provider/availability', ['is_available' => true])
            ->assertStatus(409)
            ->assertJsonPath('success', false);

        // Once admin activates the account, availability works.
        $auth['user']->provider()->update(['status' => AccountStatus::Active->value]);

        $this->withToken($auth['token'])->putJson('/api/v1/provider/availability', ['is_available' => true])
            ->assertOk()->assertJsonPath('data.is_available', true);
    }

    public function test_provider_updates_location(): void
    {
        $auth = $this->registerViaOtp('+213662000555');
        $wilaya = Wilaya::create(['code' => '19', 'name_fr' => 'Sétif', 'active' => true]);
        $commune = Commune::create(['wilaya_id' => $wilaya->id, 'code' => '19001', 'name_fr' => 'Sétif']);
        $service = Service::create(['code' => 'SRV-P3', 'name_fr' => 'Diag test', 'type' => 'planifie', 'active' => true]);

        $this->withToken($auth['token'])->postJson('/api/v1/provider/register', [
            'full_name' => 'Amine',
            'birth_date' => '1988-02-14',
            'wilaya_id' => $wilaya->id,
            'commune_id' => $commune->id,
            'coverage_radius_km' => 10,
            'service_ids' => [$service->id],
            'vehicle' => ['vehicle_type' => 'voiture', 'brand' => 'VW', 'model' => 'Golf', 'plate' => '190-01-1-19', 'year' => 2015],
        ])->assertCreated();
        $auth['user']->provider()->update(['status' => AccountStatus::Active->value]);

        $this->withToken($auth['token'])->putJson('/api/v1/provider/location', [
            'lat' => 36.1910,
            'lng' => 5.4033,
        ])->assertOk()->assertJsonPath('data.current_lat', 36.1910);
    }
}
