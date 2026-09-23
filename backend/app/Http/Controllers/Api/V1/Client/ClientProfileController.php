<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Client\UpdateClientProfileRequest;
use App\Models\Client;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientProfileController extends Controller
{
    private const PUBLIC_FIELDS = ['id', 'first_name', 'last_name', 'email', 'wilaya_id', 'profile_photo', 'language', 'status'];

    public function show(Request $request): JsonResponse
    {
        $client = $this->client($request);

        return ApiResponse::success($this->payload($client));
    }

    public function update(UpdateClientProfileRequest $request): JsonResponse
    {
        $client = $this->client($request);
        $client->update($request->safe()->all());

        return ApiResponse::success($this->payload($client), 'Profil mis à jour.');
    }

    private function client(Request $request): Client
    {
        return $request->user()->client()->firstOrCreate(['user_id' => $request->user()->id]);
    }

    private function payload(Client $client): array
    {
        return array_merge(
            $client->only(self::PUBLIC_FIELDS),
            ['phone_number' => $client->user?->phone_number]
        );
    }
}
