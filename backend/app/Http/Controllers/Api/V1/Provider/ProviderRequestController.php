<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\AccountStatus;
use App\Enums\RequestStatus;
use App\Exceptions\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\CompleteRequestRequest;
use App\Http\Requests\Api\V1\Provider\UpdateRequestStatusRequest;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Services\Requests\RequestLifecycleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderRequestController extends Controller
{
    public function __construct(private readonly RequestLifecycleService $lifecycle) {}

    /**
     * GET /api/v1/provider/requests/active
     *
     * Current + upcoming interventions for the authenticated provider.
     * Upcoming = planned requests within range that match the provider
     * (BRD §7.4.1 message "Mise à jour de la position" nearby list).
     */
    public function active(Request $request): JsonResponse
    {
        $provider = $this->provider($request);

        $active = ServiceRequest::query()
            ->where('provider_id', $provider->id)
            ->whereIn('status', [
                RequestStatus::Accepted->value,
                RequestStatus::EnRoute->value,
                RequestStatus::OnSite->value,
            ])
            ->with(['service:id,code,name_fr', 'photos'])
            ->orderByDesc('created_at')
            ->get();

        $countLimit = (int) Setting::get('request.collection_max_count', 5);
        $upcoming = ServiceRequest::query()
            ->where('status', RequestStatus::Searching->value)
            ->where('type', 'planifie')
            ->where('scheduled_at', '>=', now())
            ->whereDoesntHave('assignments', fn ($q) => $q->where('provider_id', $provider->id)->where('status', '!=', 'rejected'))
            ->with(['service:id,code,name_fr'])
            ->orderBy('scheduled_at')
            ->limit($countLimit)
            ->get()
            ->filter(fn ($r) => $this->matchesUpcoming($provider, $r))
            ->values();

        return ApiResponse::success([
            'active' => $active->map(fn ($r) => $this->intervention($r))->values(),
            'upcoming_count' => $upcoming->count(),
        ]);
    }

    /**
     * POST /api/v1/provider/requests/{serviceRequest}/accept
     */
    public function accept(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $provider = $this->provider($request);

        $this->ensureOneActiveIntervention($provider);

        if (! $serviceRequest->isActive()) {
            throw new ApiException('Cette demande n\'est plus disponible.', 410, 'request_no_longer_available');
        }

        $isAssigned = $serviceRequest->assignments()
            ->where('provider_id', $provider->id)
            ->whereIn('status', ['invited', 'accepted'])
            ->exists();

        if (! $isAssigned && $serviceRequest->provider_id !== null && $serviceRequest->provider_id !== $provider->id) {
            throw new ApiException('Cette demande a déjà été prise en charge.', 409, 'request_already_accepted');
        }

        if ($serviceRequest->status->value !== RequestStatus::Searching->value) {
            throw new ApiException('Cette demande n\'est plus en attente.', 409, 'invalid_status_transition');
        }

        $serviceRequest->forceFill(['provider_id' => $provider->id])->save();

        $this->lifecycle->transition($serviceRequest, RequestStatus::Accepted, 'provider', $provider->user_id);

        $serviceRequest->assignments()
            ->where('provider_id', $provider->id)
            ->where('status', 'invited')
            ->update(['status' => 'accepted', 'responded_at' => now()]);

        return ApiResponse::success(['id' => $serviceRequest->id, 'status' => $serviceRequest->status->value], 'Demande acceptée.');
    }

    /**
     * POST /api/v1/provider/requests/{serviceRequest}/status
     *
     * en_route -> sur_place progression.
     */
    public function updateStatus(UpdateRequestStatusRequest $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $this->authorizeProvider($request, $serviceRequest);

        $to = RequestStatus::tryFrom($request->input('status'));
        $this->lifecycle->transition($serviceRequest, $to, 'provider', $request->user()->id);

        return ApiResponse::success(['id' => $serviceRequest->id, 'status' => $serviceRequest->status->value], 'Statut mis à jour.');
    }

    /**
     * POST /api/v1/provider/requests/{serviceRequest}/complete
     */
    public function complete(CompleteRequestRequest $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $this->authorizeProvider($request, $serviceRequest);

        $serviceRequest->forceFill([
            'amount_cash' => $request->input('amount_cash'),
            'provider_internal_note' => $request->input('provider_note'),
        ])->save();

        $this->lifecycle->transition($serviceRequest, RequestStatus::Completed, 'provider', $request->user()->id, $request->input('provider_note'));

        return ApiResponse::success(['id' => $serviceRequest->id, 'status' => $serviceRequest->status->value, 'amount_cash' => $request->input('amount_cash')], 'Intervention terminée. Client notifié.');
    }

    /**
     * POST /api/v1/provider/requests/{serviceRequest}/reject
     */
    public function reject(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $provider = $this->provider($request);

        $serviceRequest->assignments()
            ->where('provider_id', $provider->id)
            ->where('status', 'invited')
            ->update(['status' => 'rejected', 'responded_at' => now()]);

        return ApiResponse::success(['id' => $serviceRequest->id], 'Demande refusée.');
    }

    private function provider(Request $request)
    {
        // Fresh query: the authenticated user instance may carry a stale,
        // relation-cached profile (e.g. before registration completed).
        $provider = $request->user()->provider()->first();

        if ($provider === null) {
            throw new ApiException('Profil prestataire non créé.', 404, 'provider_not_found');
        }
        if ($provider->status !== AccountStatus::Active) {
            throw new ApiException('Compte non actif. En attente de vérification.', 409, 'provider_not_active');
        }

        return $provider;
    }

    private function authorizeProvider(Request $request, ServiceRequest $serviceRequest): void
    {
        $provider = $this->provider($request);

        if ($serviceRequest->provider_id !== $provider->id) {
            throw new ApiException('Cette demande ne vous est pas assignée.', 403, 'forbidden');
        }
    }

    private function ensureOneActiveIntervention($provider): void
    {
        if ($provider->hasActiveIntervention()) {
            throw new ApiException('Vous avez déjà une intervention en cours.', 409, 'active_intervention_exists');
        }
    }

    private function matchesUpcoming($provider, ServiceRequest $request): bool
    {
        $serviceCovered = $provider->services()->where('service_id', $request->service_id)->exists();
        $wilayaCovered = $provider->coverageWilayas()->where('wilaya_id', $request->client?->wilaya_id)->exists();

        return $serviceCovered && $wilayaCovered;
    }

    private function intervention(ServiceRequest $serviceRequest): array
    {
        $serviceRequest->load('client.user:id,phone_number', 'service:id,code,name_fr');

        return [
            'id' => $serviceRequest->id,
            'type' => $serviceRequest->type->value,
            'status' => $serviceRequest->status->value,
            'description' => $serviceRequest->description,
            'client_address' => $serviceRequest->client_address,
            'client_lat' => $serviceRequest->client_lat,
            'client_lng' => $serviceRequest->client_lng,
            'scheduled_at' => $serviceRequest->scheduled_at?->toIso8601String(),
            'accepted_at' => $serviceRequest->accepted_at?->toIso8601String(),
            'service' => $serviceRequest->service?->only(['id', 'code', 'name_fr']),
            'client' => [
                'first_name' => $serviceRequest->client?->first_name,
                'last_name' => $serviceRequest->client?->last_name,
                'phone_number' => $serviceRequest->client?->user?->phone_number,
            ],
        ];
    }
}
