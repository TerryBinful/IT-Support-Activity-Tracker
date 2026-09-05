<?php

use App\Console\Commands\CreateMonthlyReportReminders;
use App\Console\Commands\GenerateRecurringActivitiesCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CreateMonthlyReportReminders::class)->weeklyOn(5, '08:00')->withoutOverlapping();
Schedule::command(GenerateRecurringActivitiesCommand::class)->dailyAt('06:00')->withoutOverlapping();
