<?php

namespace App\Console\Commands;

use App\Services\MesaHoldService;
use Illuminate\Console\Command;

class ReleaseExpiredHolds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'holds:release-expired';

    /**
     * The console command description.
     */
    protected $description = 'Liberar bloqueos temporales de mesas que han expirado';

    /**
     * Execute the console command.
     */
    public function handle(MesaHoldService $service): int
    {
        $released = $service->releaseExpiredHolds();

        if ($released > 0) {
            $this->info("Se liberaron {$released} bloqueos expirados.");
        }

        return 0;
    }
}
