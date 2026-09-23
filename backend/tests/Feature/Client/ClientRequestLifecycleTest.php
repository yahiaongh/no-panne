<?php

namespace Tests\Feature\Client;

use App\Enums\AccountStatus;
use App\Models\Provider;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Wilaya;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAuth;
use Tests\TestCase;

class ClientRequestLifecycleTest extends TestCase
{
    use InteractsWithAuth, RefreshDatabase;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Service::create([
            'code' => 'SRV-TST',
            'name_fr' => 'Dépannage test',
            'type' => 'urgence',
            'active' => true,
            'sort_order' => 99,
        ]);
    }

    public function test_client_full_request_lifecycle(): void
    {
        $client = $this->registerViaOtp('+213661000111');
        $wilaya = Wilaya::create(['code' => '16', 'name_fr' => 'Alger', 'active' => true]);

        $this->withToken($client['token'])->putJson('/api/v1/client/profile', [
            'first_name' => 'Yacine',
            'last_name' => 'BENALI',
            'wilaya_id' => $wilaya->id,
        ])->assertOk();

        // Two nearby providers covering the service.
        $provider1 = $this->makeActiveProvider([36.7538, 3.0588]);
        $provider2 = $this->makeActiveProvider([36.8500, 3.0500]);

        // Client creates an emergency request.
        $response = $this->withToken($client['token'])->postJson('/api/v1/client/requests', [
            'service_id' => $this->service->id,
            'type' => 'urgence',
            'description' => 'Panne moteur sur le boulevard',
            'client_address' => '23 Rue Didouche Mourad, Alger',
            'client_lat' => 36.7538,
            'client_lng' => 3.0588,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'en_recherche')
            ->assertJsonPath('data.assignment.status', 'invited');

        $requestId = $response->json('data.id');

        // The closest provider is invited.
        $this->assertDatabaseHas('request_assignments', [
            'request_id' => $requestId,
            'provider_id' => $provider1['user']->provider->id,
            'status' => 'invited',
        ]);

        // Provider 1 accepts.
        $accept = $this->withToken($provider1['token'])->postJson("/api/v1/provider/requests/{$requestId}/accept");
        $accept->assertOk()->assertJsonPath('data.status', 'acceptee');

        // Client can now see the provider phone in the request detail.
        $detail = $this->withToken($client['token'])->getJson("/api/v1/client/requests/{$requestId}");
        $detail->assertOk()->assertJsonPath('data.provider.id', $provider1['user']->provider->id);

        // Progress en_route -> sur_place.
        $this->withToken($provider1['token'])->postJson("/api/v1/provider/requests/{$requestId}/status", ['status' => 'en_route'])->assertOk();
        $this->withToken($provider1['token'])->postJson("/api/v1/provider/requests/{$requestId}/status", ['status' => 'sur_place'])->assertOk();

        // Complete with cash amount.
        $this->withToken($provider1['token'])->postJson("/api/v1/provider/requests/{$requestId}/complete", [
            'amount_cash' => 3500,
            'provider_note' => 'Batterie remplacée',
        ])->assertOk()->assertJsonPath('data.status', 'terminee');

        // A second provider can no longer accept (request is finished).
        $this->withToken($provider2['token'])->postJson("/api/v1/provider/requests/{$requestId}/accept")
            ->assertStatus(410)
            ->assertJsonPath('code', 'request_no_longer_available');

        // Terminal request: status history recorded.
        $this->assertDatabaseHas('request_status_history', [
            'request_id' => $requestId,
            'to_status' => 'terminee',
        ]);

        // Client cannot cancel a finished request.
        $this->withToken($client['token'])->postJson("/api/v1/client/requests/{$requestId}/cancel", ['reason' => 'trop tard'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'request_already_closed');
    }

    public function test_client_can_cancel_a_searching_request(): void
    {
        $client = $this->registerViaOtp('+213661000222');

        $created = $this->withToken($client['token'])->postJson('/api/v1/client/requests', [
            'service_id' => $this->service->id,
            'type' => 'urgence',
            'description' => 'Crevaison',
            'client_address' => 'RN1, Blida',
            'client_lat' => 36.47,
            'client_lng' => 2.83,
        ])->assertCreated();

        $requestId = $created->json('data.id');

        $this->withToken($client['token'])->postJson("/api/v1/client/requests/{$requestId}/cancel", [
            'reason' => 'Plus besoin',
        ])->assertOk()->assertJsonPath('data.status', 'annulee_client');
    }

    private function makeActiveProvider(array $coords): array
    {
        $user = User::factory()->provider()->create();
        $provider = Provider::factory()->create([
            'user_id' => $user->id,
            'status' => AccountStatus::Active,
            'is_available' => true,
            'current_lat' => $coords[0],
            'current_lng' => $coords[1],
            'coverage_radius_km' => 30,
        ]);
        $provider->services()->attach($this->service->id, ['indicative_price' => 2000]);
        Vehicle::create([
            'provider_id' => $provider->id,
            'vehicle_type' => 'voiture',
            'brand' => 'Renault',
            'model' => 'Clio 3',
            'plate' => fake()->unique()->bothify('###-##-###'),
            'is_active' => true,
        ]);

        return ['token' => $user->createToken('test')->plainTextToken, 'user' => $user];
    }
}
