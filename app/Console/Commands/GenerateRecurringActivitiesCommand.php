<?php

namespace App\Console\Commands;

use App\Services\Recurring\GenerateRecurringActivities;
use Illuminate\Console\Command;

class GenerateRecurringActivitiesCommand extends Command
{
    protected $signature = 'activities:generate-recurring';

    protected $description = 'Generate activities from active recurring definitions.';

    public function handle(GenerateRecurringActivities $generator): int
    {
        $count = $generator->handle();
        $this->info("Generated {$count} recurring activities.");

        return self::SUCCESS;
    }
}
