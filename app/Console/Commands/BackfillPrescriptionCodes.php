<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prescription;

class BackfillPrescriptionCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prescriptions:backfill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill prescription_code for existing prescriptions from related medicines or generate RX{id}';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting backfill of prescription_code...');

        $count = 0;

        Prescription::with('medicines')->chunk(100, function ($prescriptions) use (&$count) {
            foreach ($prescriptions as $prescription) {
                if (!empty($prescription->prescription_code)) {
                    continue;
                }

                $codes = $prescription->medicines->pluck('prescription_id')->filter()->unique();
                if ($codes->isNotEmpty()) {
                    $prescription->prescription_code = $codes->first();
                } else {
                    $prescription->prescription_code = 'RX' . $prescription->id;
                }

                $prescription->saveQuietly();
                $count++;
                $this->line("Updated prescription #{$prescription->id} -> {$prescription->prescription_code}");
            }
        });

        $this->info("Backfilled {$count} prescriptions.");

        return 0;
    }
}
