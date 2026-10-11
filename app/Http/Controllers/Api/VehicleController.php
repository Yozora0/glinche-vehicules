<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleDetailResource;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\Glinche\VehicleSynchronizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VehicleController extends Controller
{
    public function __construct(private readonly VehicleSynchronizer $synchronizer)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'brand' => ['nullable', 'string', 'max:100'],
        ]);

        $this->synchronizer->syncIfStale((int) config('services.glinche.sync_ttl'));

        $vehicles = Vehicle::query()
            ->ofBrand($validated['brand'] ?? null)
            ->orderBy('brand')
            ->orderBy('model')
            ->orderBy('price')
            ->get();

        return VehicleResource::collection($vehicles)->additional([
            'meta' => [
                'count' => $vehicles->count(),
                'synced_at' => $this->synchronizer->lastSyncedAt()?->toIso8601String(),
            ],
        ]);
    }

    public function show(Request $request, Vehicle $vehicle): JsonResponse
    {
        if ($vehicle->raw === null) {
            $this->synchronizer->sync();
            $vehicle->refresh();
        }

        return response()->json((new VehicleDetailResource($vehicle))->resolve($request));
    }

    public function brands(): JsonResponse
    {
        $this->synchronizer->syncIfStale((int) config('services.glinche.sync_ttl'));

        $brands = Vehicle::query()
            ->select('brand')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('brand')
            ->orderBy('brand')
            ->get()
            ->map(fn (Vehicle $row) => ['name' => $row->brand, 'count' => (int) $row->getAttribute('count')]);

        return response()->json(['data' => $brands]);
    }

    public function sync(): JsonResponse
    {
        $stats = $this->synchronizer->sync();

        return response()->json([
            'message' => 'Synchronisation terminée.',
            'stats' => $stats,
            'synced_at' => $this->synchronizer->lastSyncedAt()?->toIso8601String(),
        ]);
    }
}
