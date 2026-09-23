<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Wilaya;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferenceDataController extends Controller
{
    /**
     * GET /api/v1/services — active service catalogue (BRD §5.1).
     */
    public function services(Request $request): JsonResponse
    {
        $services = Service::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get(['id', 'code', 'name_fr', 'name_ar', 'type', 'description_fr', 'description_ar', 'active']);

        return ApiResponse::success($services, meta: ['count' => $services->count()]);
    }

    /**
     * GET /api/v1/wilayas — supported wilayas (BRD §8.7).
     */
    public function wilayas(Request $request): JsonResponse
    {
        $wilayas = Wilaya::query()
            ->where('active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_fr', 'name_ar', 'lat', 'lng']);

        return ApiResponse::success($wilayas, meta: ['count' => $wilayas->count()]);
    }

    /**
     * GET /api/v1/wilayas/{wilaya}/communes — communes of a wilaya.
     */
    public function communes(Wilaya $wilaya, Request $request): JsonResponse
    {
        $communes = $wilaya->communes()
            ->orderBy('name_fr')
            ->get(['id', 'code', 'name_fr', 'name_ar']);

        return ApiResponse::success($communes, meta: ['count' => $communes->count()]);
    }
}
