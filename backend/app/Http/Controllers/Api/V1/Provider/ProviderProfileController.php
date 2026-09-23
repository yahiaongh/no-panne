<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\AccountStatus;
use App\Exceptions\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\UpdateProviderAvailabilityRequest;
use App\Http\Requests\Api\V1\Provider\UpdateProviderLocationRequest;
use App\Http\Requests\Api\V1\Provider\UpdateProviderProfileRequest;
use App\Models\Provider;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderProfileController extends Controller
{
    private const PUBLIC_FIELDS = [
        'id', 'full_name', 'birth_date', 'wilaya_id', 'commune_id',
        'coverage_radius_km', 'is_available', 'current_lat', 'current_lng',
        'rating_avg', 'rating_count', 'status', 'profile_photo',
    ];

    public function show(Request $request): JsonResponse
    {
        $provider = $this->provider($request);
        $provider->load('services:id,code,name_fr,type', 'coverageWilayas:id,code,name_fr');

        return ApiResponse::success($this->payload($provider));
    }

    public function update(UpdateProviderProfileRequest $request): JsonResponse
    {
        $provider = $this->provider($request);
        $validated = $request->safe()->all();

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('providers/'.$provider->user_id.'/photo', config('services.storage.document_disk'));
        }

        $provider->update(collect($validated)->except(['service_ids', 'coverage_wilaya_ids'])->all());

        if (isset($validated['service_ids'])) {
            $provider->services()->sync($validated['service_ids']);
        }
        if (isset($validated['coverage_wilaya_ids'])) {
            $provider->coverageWilayas()->sync(array_merge([$provider->wilaya_id], $validated['coverage_wilaya_ids']));
        }

        $provider->refresh();

        return ApiResponse::success($this->payload($provider), 'Profil mis à jour.');
    }

    public function availability(UpdateProviderAvailabilityRequest $request): JsonResponse
    {
        $provider = $this->provider($request);

        if ($request->boolean('is_available') && $provider->status->value !== AccountStatus::Active->value) {
            throw new ApiException('Compte non actif. En attente de vérification.', 409, 'provider_not_active');
        }

        $provider->forceFill(['is_available' => $request->boolean('is_available')])->save();

        return ApiResponse::success(['is_available' => $provider->is_available], 'Disponibilité mise à jour.');
    }

    public function location(UpdateProviderLocationRequest $request): JsonResponse
    {
        $provider = $this->provider($request);
        $provider->forceFill([
            'current_lat' => $request->float('lat'),
            'current_lng' => $request->float('lng'),
        ])->save();

        return ApiResponse::success(['current_lat' => $provider->current_lat, 'current_lng' => $provider->current_lng], 'Position mise à jour.');
    }

    private function provider(Request $request): Provider
    {
        // Fresh query: the authenticated user instance may carry a stale,
        // relation-cached profile (e.g. before registration completed).
        $provider = $request->user()->provider()->first();

        if ($provider === null) {
            throw new ApiException('Profil prestataire non créé.', 404, 'provider_not_found');
        }

        return $provider;
    }

    private function payload(Provider $provider): array
    {
        return [
            ...$provider->only(self::PUBLIC_FIELDS),
            'phone_number' => $provider->user?->phone_number,
            'email' => $provider->user?->email,
            'services' => $provider->services->map(fn ($s) => $s->only(['id', 'code', 'name_fr', 'type', 'pivot']))->values(),
            'coverage_wilayas' => $provider->coverageWilayas->map(fn ($w) => $w->only(['id', 'code', 'name_fr']))->values(),
            'active_vehicle' => $provider->activeVehicle()?->only(['id', 'vehicle_type', 'brand', 'model', 'plate', 'year']),
        ];
    }
}
