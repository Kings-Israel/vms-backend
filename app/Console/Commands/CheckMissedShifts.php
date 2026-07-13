<?php

namespace App\Console\Commands;

use App\Models\Shift;
use Illuminate\Console\Command;

class CheckMissedShifts extends Command
{
    protected $signature = 'shifts:check-missed';
    protected $description = 'Mark shifts as missed if officer has not started within 30 minutes';

    public function handle(): void
    {
        $missed = Shift::where('status', 'scheduled')
            ->where('starts_at', '<', now()->subMinutes(30))
            ->get();

        foreach ($missed as $shift) {
            $shift->update(['status' => 'missed']);
            activity()->performedOn($shift)->log('Shift marked as missed');
        }

        $this->info("Marked {$missed->count()} shift(s) as missed.");
    }
}
