<?php

namespace App\Console\Commands;

use App\Services\Glinche\GlincheApiException;
use App\Services\Glinche\VehicleSynchronizer;
use Illuminate\Console\Command;

class SyncVehicles extends Command
{
    protected $signature = 'vehicles:sync';

    protected $description = 'Récupère les véhicules depuis l\'API Glinche (V1) et met à jour la base';

    public function handle(VehicleSynchronizer $synchronizer): int
    {
        $this->info('Synchronisation des véhicules depuis l\'API Glinche...');

        try {
            $stats = $synchronizer->sync();
        } catch (GlincheApiException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Créés', 'Mis à jour', 'Retirés', 'Ignorés'],
            [[$stats['created'], $stats['updated'], $stats['removed'], $stats['skipped']]],
        );

        return self::SUCCESS;
    }
}
