<?php
use App\Console\Commands\CreateMonthlyReportReminders;
use Illuminate\Support\Facades\Schedule;
Schedule::command(CreateMonthlyReportReminders::class)->weeklyOn(5,'08:00')->withoutOverlapping();
