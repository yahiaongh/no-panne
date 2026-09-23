<?php

declare(strict_types=1);

namespace App\Services\Requests;

use App\Enums\RequestStatus;
use App\Exceptions\Api\ApiException;
use App\Models\RequestStatusHistory;
use App\Models\ServiceRequest;

/**
 * Central status-transition guard and history recorder (BRD §10.4).
 *
 * Allowed transitions:
 *   searching    -> accepted | annulee_client | annulee_prestataire | aucun_prestataire | expiree
 *   accepted     -> en_route  | annulee_client | annulee_prestataire | expiree
 *   en_route     -> sur_place | annulee_client | annulee_prestataire
 *   sur_place    -> terminee  | annulee_client
 *   ANY          -> (no transition out of a terminal state)
 */
class RequestLifecycleService
{
    private const LOCKED = [
        RequestStatus::Completed,
        RequestStatus::CancelledByClient,
        RequestStatus::CancelledByProvider,
        RequestStatus::NoProvider,
        RequestStatus::Expired,
    ];

    private const ALLOWED = [
        RequestStatus::Searching->value => [
            RequestStatus::Searching->value, // initial status recording
            RequestStatus::Accepted->value,
            RequestStatus::CancelledByClient->value,
            RequestStatus::CancelledByProvider->value,
            RequestStatus::NoProvider->value,
            RequestStatus::Expired->value,
        ],
        RequestStatus::Accepted->value => [
            RequestStatus::EnRoute->value,
            RequestStatus::CancelledByClient->value,
            RequestStatus::CancelledByProvider->value,
            RequestStatus::Expired->value,
        ],
        RequestStatus::EnRoute->value => [
            RequestStatus::OnSite->value,
            RequestStatus::CancelledByClient->value,
            RequestStatus::CancelledByProvider->value,
        ],
        RequestStatus::OnSite->value => [
            RequestStatus::Completed->value,
            RequestStatus::CancelledByClient->value,
        ],
    ];

    public function transition(
        ServiceRequest $request,
        RequestStatus $to,
        string $changedByType = 'system',
        ?string $changedById = null,
        ?string $note = null,
    ): ServiceRequest {
        $from = $request->status;

        if (in_array($from, self::LOCKED, true)) {
            throw new ApiException('La demande est déjà clôturée.', 422, 'request_already_closed');
        }

        if (! isset(self::ALLOWED[$from->value]) || ! in_array($to->value, self::ALLOWED[$from->value], true)) {
            throw new ApiException('Transition de statut non autorisée.', 422, 'invalid_status_transition');
        }

        $request->status = $to;
        $this->applyTimestamps($request, $to);

        $request->save();

        RequestStatusHistory::query()->create([
            'request_id' => $request->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'changed_by_type' => $changedByType,
            'changed_by_id' => $changedById,
            'note' => $note,
        ]);

        return $request;
    }

    private function applyTimestamps(ServiceRequest $request, RequestStatus $to): void
    {
        $now = now();

        $request->accepted_at = match ($to) {
            RequestStatus::Accepted => $now,
            default => $request->accepted_at,
        };

        $request->arrived_at = match ($to) {
            RequestStatus::OnSite => $now,
            default => $request->arrived_at,
        };

        $request->completed_at = match ($to) {
            RequestStatus::Completed => $now,
            default => $request->completed_at,
        };
    }
}
