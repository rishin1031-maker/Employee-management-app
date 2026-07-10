<?php

namespace App\Console\Commands;

use App\Services\ContinuousSessionService;
use Illuminate\Console\Command;

class EnforceContinuousSessionCommand extends Command
{
    protected $signature = 'attendance:enforce-continuous-session';

    protected $description = 'Send continuous-session reminders and auto-checkout overdue employees';

    public function handle(ContinuousSessionService $service): int
    {
        $result = $service->enforce();

        $this->info("Reminders: {$result['reminders']}, Auto-checkouts: {$result['auto_checkouts']}");

        return self::SUCCESS;
    }
}
