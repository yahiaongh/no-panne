<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Exceptions\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\RegisterProviderRequest;
use App\Models\Provider;
use App\Models\Vehicle;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProviderRegisterController extends Controller
{
    /**
     * POST /api/v1/provider/register
     *
     * Converts the verified phone account into a provider profile (BRD §7.2):
     *  - the phone account is the unique identity (one phone = one account);
     *  - a provider is created in "en_attente" state until the admin verifies
     *    identity documents;
     *  - registering is refused when the account already carries client data.
     */
    public function store(RegisterProviderRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isProvider()) {
            throw new ApiException('Compte prestataire déjà enregistré.', 409, 'already_registered');
        }

        if ($user->client?->exists && $user->client->requests()->exists()) {
            throw new ApiException(
                'Ce compte possède déjà un historique client. Utilisez un autre téléphone.',
                409,
                'client_history_conflict'
            );
        }

        $user->client?->forceDelete();
        $user->forceFill(['role_type' => UserRole::Provider])->save();

        $provider = Provider::query()->create([
            'user_id' => $user->id,
            'full_name' => $request->input('full_name'),
            'birth_date' => $request->input('birth_date'),
            'wilaya_id' => $request->input('wilaya_id'),
            'commune_id' => $request->input('commune_id'),
            'coverage_radius_km' => $request->input('coverage_radius_km'),
            'current_lat' => $request->input('current_lat'),
            'current_lng' => $request->input('current_lng'),
            'profile_photo' => $request->hasFile('profile_photo')
                ? $request->file('profile_photo')->store('providers/'.$user->id.'/photo', config('services.storage.document_disk'))
                : null,
            'status' => AccountStatus::Pending,
            'is_available' => false,
        ]);

        $provider->services()->sync($request->input('service_ids'));
        if ($request->filled('coverage_wilaya_ids')) {
            $provider->coverageWilayas()->sync(array_merge([$request->input('wilaya_id')], $request->input('coverage_wilaya_ids')));
        } else {
            $provider->coverageWilayas()->sync([$request->input('wilaya_id')]);
        }

        Vehicle::query()->create([
            'provider_id' => $provider->id,
            'vehicle_type' => $request->input('vehicle.vehicle_type'),
            'brand' => $request->input('vehicle.brand'),
            'model' => $request->input('vehicle.model'),
            'plate' => $request->input('vehicle.plate'),
            'year' => $request->input('vehicle.year'),
            'is_active' => true,
        ]);

        return ApiResponse::created([
            'id' => $provider->id,
            'full_name' => $provider->full_name,
            'status' => $provider->status->value,
            'message' => 'Profil prestataire créé. En attente de vérification par l\'administration.',
        ]);
    }
}
