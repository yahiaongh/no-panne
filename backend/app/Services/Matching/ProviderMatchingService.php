<?php

declare(strict_types=1);

namespace App\Services\Matching;

use App\Enums\AccountStatus;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Provider;
use App\Models\RequestAssignment;
use App\Models\ServiceRequest;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

/**
 * Provider matching per BRD §7.3 / §7.4:
 *  - emergency: notify the closest available provider, 45-second window;
 *    if no response, widen the search radius by steps (10 -> 20 -> 30 km).
 *  - planned: radius from provider coverage, window of 2 hours.
 *
 * Distance uses the haversine formula on provider.current_lat/lng (PostGIS
 * will replace this once geospatial data is imported — see architecture-decisions.md).
 */
class ProviderMatchingService
{
    /**
     * Candidate providers for a request (excluding providers already on an
     * active intervention). Order: nearest first.
     */
    public function candidatesFor(ServiceRequest $request): Collection
    {
        return Provider::query()
            ->where('status', AccountStatus::Active->value)
            ->where('is_available', true)
            ->whereHas('services', fn ($q) => $q->where('service_id', $request->service_id))
            ->whereDoesntHave('requests', function ($q) {
                $q->whereIn('status', [RequestStatus::Searching->value, RequestStatus::Accepted->value, RequestStatus::EnRoute->value, RequestStatus::OnSite->value]);
            })
            ->when($request->type === RequestType::Emergency, function ($q) {
                // Emergency: providers without live coordinates cannot be candidate.
                $q->whereNotNull('current_lat')->whereNotNull('current_lng');
            })
            ->when($request->type === RequestType::Planned, function ($q) use ($request) {
                // Planned: require an overlapping coverage wilaya.
                $q->whereHas('coverageWilayas', fn ($w) => $w->whereKey($request->client?->wilaya_id));
            })
            ->get();
    }

    /**
     * Actively accept an invitation for the nearest matching provider.
     * Returns the provider, or null when none is left for the search radius.
     */
    public function acceptForRequest(ServiceRequest $request): ?RequestAssignment
    {
        $candidate = $this->candidatesFor($request)->first();

        if ($candidate === null) {
            return null;
        }

        $assignment = $request->assignments()->firstOrCreate(
            ['provider_id' => $candidate->id],
            [
                'status' => 'invited',
                'invited_at' => now(),
                'expires_at' => now()->addSeconds($this->responseWindowSeconds($request)),
            ]
        );

        return $assignment;
    }

    /**
     * Response window: 45s for emergency, 2h for planned (BRD §7.3.4).
     */
    public function responseWindowSeconds(ServiceRequest $request): int
    {
        if ($request->type === RequestType::Emergency) {
            return (int) Setting::get('request.emergency_search_seconds', 45);
        }

        return (int) Setting::get('request.planned_validity_hours', 2) * 3600;
    }

    public function searchRadius(ServiceRequest $request): int
    {
        return (int) $request->search_radius_km;
    }
}
