<?php

namespace App\Services\Glinche;

use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class VehicleSynchronizer
{
    private const LAST_SYNC_CACHE_KEY = 'glinche.vehicles.last_sync';

    private const LOCK_KEY = 'glinche.vehicles.sync';

    public function __construct(
        private readonly GlincheClient $client,
        private readonly AdvertMapper $mapper,
    ) {
    }

    public function sync(): array
    {
        $adverts = $this->client->vehicles();

        $stats = ['created' => 0, 'updated' => 0, 'removed' => 0, 'skipped' => 0];
        $seen = [];
        $now = Carbon::now();

        DB::transaction(function () use ($adverts, &$stats, &$seen, $now) {
            foreach ($adverts as $advert) {
                $attributes = is_array($advert) ? $this->mapper->map($advert) : null;

                if ($attributes === null || $attributes['is_deleted']) {
                    $stats['skipped']++;

                    continue;
                }

                unset($attributes['is_deleted']);
                $attributes['synced_at'] = $now;

                $vehicle = Vehicle::updateOrCreate(
                    ['reference' => $attributes['reference']],
                    $attributes,
                );

                $vehicle->wasRecentlyCreated ? $stats['created']++ : $stats['updated']++;
                $seen[] = $vehicle->reference;
            }

            $stats['removed'] = Vehicle::query()
                ->when($seen !== [], fn ($q) => $q->whereNotIn('reference', $seen))
                ->delete();
        });

        Cache::forever(self::LAST_SYNC_CACHE_KEY, $now->toIso8601String());

        return $stats;
    }

    public function syncIfStale(int $ttlMinutes): void
    {
        $isEmpty = ! Vehicle::query()->exists();

        if (! $isEmpty && ($ttlMinutes <= 0 || ! $this->isStale($ttlMinutes))) {
            return;
        }

        $lock = Cache::lock(self::LOCK_KEY, 60);

        if (! $lock->get()) {
            return;
        }

        try {
            $this->sync();
        } catch (Throwable $e) {
            if ($isEmpty) {
                throw $e;
            }

            Log::warning('Synchro Glinche échouée, données en base conservées.', ['error' => $e->getMessage()]);
        } finally {
            $lock->release();
        }
    }

    public function lastSyncedAt(): ?Carbon
    {
        $value = Cache::get(self::LAST_SYNC_CACHE_KEY);

        return $value ? Carbon::parse($value) : null;
    }

    private function isStale(int $ttlMinutes): bool
    {
        $last = $this->lastSyncedAt();

        return $last === null || $last->lt(Carbon::now()->subMinutes($ttlMinutes));
    }
}
