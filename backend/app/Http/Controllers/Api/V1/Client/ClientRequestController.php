<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\CancelledBy;
use App\Enums\RequestStatus;
use App\Exceptions\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Client\CancelRequestRequest;
use App\Http\Requests\Api\V1\Client\StoreRequestRequest;
use App\Models\RequestPhoto;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Services\Matching\ProviderMatchingService;
use App\Services\Requests\RequestLifecycleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientRequestController extends Controller
{
    public function __construct(
        private readonly RequestLifecycleService $lifecycle,
        private readonly ProviderMatchingService $matching,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $requests = ServiceRequest::query()
            ->where('client_id', $request->user()->client?->id)
            ->with(['service:id,name_fr,code', 'photos'])
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return ApiResponse::success($requests->map(fn ($r) => $r->toArray()), meta: [
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
                'last_page' => $requests->lastPage(),
            ],
        ]);
    }

    public function store(StoreRequestRequest $request, RequestLifecycleService $lifecycle): JsonResponse
    {
        // firstOrCreate keeps the one-to-one relation safe even when the
        // authenticated User instance predates the Client row.
        $client = $request->user()->client()->firstOrCreate(['user_id' => $request->user()->id]);

        $serviceRequest = ServiceRequest::query()->create([
            'client_id' => $client->id,
            'service_id' => $request->input('service_id'),
            'type' => $request->input('type'),
            'description' => $request->input('description'),
            'client_address' => $request->input('client_address'),
            'client_lat' => $request->input('client_lat'),
            'client_lng' => $request->input('client_lng'),
            'estimated_budget' => $request->input('estimated_budget'),
            'scheduled_at' => $request->input('scheduled_at'),
            'status' => RequestStatus::Searching->value,
            'search_radius_km' => $request->input('search_radius_km', (int) Setting::get('search.initial_radius_km', 10)),
        ]);

        $this->attachPhotos($serviceRequest, $request);

        $lifecycle->transition($serviceRequest, RequestStatus::Searching, 'system');

        $assignment = $this->matching->acceptForRequest($serviceRequest);

        $data = $serviceRequest->load(['service:id,name_fr,code', 'photos'])->toArray();
        $data['assignment'] = $assignment?->only(['id', 'provider_id', 'status', 'expires_at']);

        return ApiResponse::created($data, 'Demande créée.');
    }

    public function show(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $this->authorizeAccess($request, $serviceRequest);

        $serviceRequest->load([
            'service',
            'photos',
            'provider.user:id,phone_number',
            'provider.wilaya:id,code,name_fr',
            'provider.commune:id,name_fr',
            'statusHistory' => fn ($q) => $q->orderByDesc('created_at'),
        ]);

        return ApiResponse::success($this->detailPayload($serviceRequest));
    }

    public function cancel(CancelRequestRequest $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $this->authorizeAccess($request, $serviceRequest);

        $this->lifecycle->transition(
            $serviceRequest,
            RequestStatus::CancelledByClient,
            'client',
            $request->user()->id,
            $request->input('reason')
        );

        $serviceRequest->forceFill(['cancelled_by' => CancelledBy::Client->value])->save();

        return ApiResponse::success(['id' => $serviceRequest->id, 'status' => $serviceRequest->status->value], 'Demande annulée.');
    }

    private function attachPhotos(ServiceRequest $serviceRequest, Request $request): void
    {
        foreach ((array) $request->file('photos', []) as $photo) {
            $path = $photo->store('requests/'.$serviceRequest->id, config('services.storage.document_disk'));

            RequestPhoto::query()->create([
                'request_id' => $serviceRequest->id,
                'path' => $path,
                'mime_type' => $photo->getMimeType(),
                'size' => $photo->getSize(),
            ]);
        }
    }

    private function authorizeAccess(Request $request, ServiceRequest $serviceRequest): void
    {
        if ((string) ($request->user()->client?->id ?? '') !== (string) $serviceRequest->client_id
            && ! $request->user()->isAdmin()) {
            throw new ApiException('Accès non autorisé.', 403, 'forbidden');
        }
    }

    private function detailPayload(ServiceRequest $serviceRequest): array
    {
        $provider = $serviceRequest->provider;

        $providerPayload = null;
        if ($provider !== null) {
            $providerPayload = [
                'id' => $provider->id,
                'full_name' => $provider->full_name,
                'profile_photo' => $provider->profile_photo,
                'rating_avg' => $provider->rating_avg,
                'rating_count' => $provider->rating_count,
                // Phone is exposed to the client only once the provider is assigned.
                'phone_number' => in_array($serviceRequest->status->value, [
                    RequestStatus::Accepted->value,
                    RequestStatus::EnRoute->value,
                    RequestStatus::OnSite->value,
                    RequestStatus::Completed->value,
                ], true) ? $provider->user?->phone_number : null,
            ];
        }

        return [
            'id' => $serviceRequest->id,
            'type' => $serviceRequest->type->value,
            'status' => $serviceRequest->status->value,
            'description' => $serviceRequest->description,
            'client_address' => $serviceRequest->client_address,
            'client_lat' => $serviceRequest->client_lat,
            'client_lng' => $serviceRequest->client_lng,
            'estimated_budget' => $serviceRequest->estimated_budget,
            'scheduled_at' => $serviceRequest->scheduled_at?->toIso8601String(),
            'amount_cash' => $serviceRequest->amount_cash,
            'search_radius_km' => $serviceRequest->search_radius_km,
            'accepted_at' => $serviceRequest->accepted_at?->toIso8601String(),
            'completed_at' => $serviceRequest->completed_at?->toIso8601String(),
            'cancellation_reason' => $serviceRequest->cancellation_reason,
            'created_at' => $serviceRequest->created_at->toIso8601String(),
            'service' => $serviceRequest->service?->only(['id', 'code', 'name_fr', 'name_ar']),
            'provider' => $providerPayload,
            'photos' => $serviceRequest->photos->map(fn ($p) => $p->only(['id', 'path']))->values(),
            'status_history' => $serviceRequest->statusHistory->map(fn ($h) => [
                'from' => $h->from_status,
                'to' => $h->to_status,
                'note' => $h->note,
                'at' => $h->created_at->toIso8601String(),
            ])->values(),
        ];
    }
}
