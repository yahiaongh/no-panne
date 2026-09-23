<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Client\NearbyProvidersRequest;
use App\Models\Provider;
use App\Services\Matching\ProviderMatchingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientProviderController extends Controller
{
    public function __construct(private readonly ProviderMatchingService $matching) {}

    /**
     * GET /api/v1/client/providers/nearby
     */
    public function nearby(NearbyProvidersRequest $request): JsonResponse
    {
        $radius = (int) $request->input('radius_km', config('services.google_maps.initial_radius_km', 10));
        $limit = min((int) $request->input('limit', 20), 50);

        $providers = Provider::query()
            ->where('status', AccountStatus::Active->value)
            ->where('is_available', true)
            ->whereHas('services', fn ($q) => $q->where('service_id', $request->input('service_id')))
            ->whereDoesntHave('requests', function ($q) {
                $q->whereIn('status', ['en_recherche', 'acceptee', 'en_route', 'sur_place']);
            })
            ->near((float) $request->input('lat'), (float) $request->input('lng'), $radius)
            ->limit($limit)
            ->get();

        return ApiResponse::success($providers->map(fn ($p) => $this->publicProvider($p)), meta: [
            'count' => $providers->count(),
            'radius_km' => $radius,
        ]);
    }

    /**
     * GET /api/v1/client/providers/{provider}
     */
    public function show(Request $request, Provider $provider): JsonResponse
    {
        $provider->load('services:id,code,name_fr,type', 'coverageWilayas:id,code,name_fr');

        return ApiResponse::success([
            ...$this->publicProvider($provider),
            'wilaya' => $provider->wilaya?->only(['id', 'code', 'name_fr']),
            'commune' => $provider->commune?->only(['id', 'code', 'name_fr']),
            'coverage_radius_km' => $provider->coverage_radius_km,
            'services' => $provider->services->map(fn ($s) => $s->only(['id', 'code', 'name_fr', 'type']))->values(),
            'coverage_wilayas' => $provider->coverageWilayas->map(fn ($w) => $w->only(['id', 'code', 'name_fr']))->values(),
            'recent_reviews' => $provider->reviews()
                ->where('moderation_status', 'visible')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($r) => [
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'client_first_name' => $r->client?->first_name,
                    'created_at' => $r->created_at->toIso8601String(),
                ])
                ->values(),
        ]);
    }

    private function publicProvider(Provider $provider): array
    {
        return [
            'id' => $provider->id,
            'full_name' => $provider->full_name,
            'profile_photo' => $provider->profile_photo,
            'rating_avg' => round($provider->rating_avg, 2),
            'rating_count' => (int) $provider->rating_count,
            'is_available' => (bool) $provider->is_available,
            'distance_km' => isset($provider->distance_km) ? round((float) $provider->distance_km, 2) : null,
        ];
    }
}
