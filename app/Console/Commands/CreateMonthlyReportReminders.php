<?php

namespace App\Console\Commands;

use App\Models\ReportReminder;
use App\Models\User;
use App\Notifications\MonthlyReportReminderNotification;
use App\Services\Reports\ReportQueryBuilder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateMonthlyReportReminders extends Command
{
    protected $signature = 'reports:remind';

    protected $description = 'Create monthly report reminders on the last Friday.';

    public function handle(ReportQueryBuilder $queryBuilder): int
    {
        $targetFriday = now()->copy()->endOfMonth();
        while ($targetFriday->dayOfWeek !== Carbon::FRIDAY) {
            $targetFriday->subDay();
        }

        if (! now()->isSameDay($targetFriday)) {
            return self::SUCCESS;
        }

        $reportMonth = now()->subMonth()->startOfMonth();
        $from = $reportMonth->toDateString();
        $to = $reportMonth->copy()->endOfMonth()->toDateString();
        $label = $reportMonth->format('F Y');

        User::query()->each(function (User $user) use ($from, $to, $label, $queryBuilder) {
            $summary = $queryBuilder->summary($user, $from, $to);

            ReportReminder::firstOrCreate(
                ['user_id' => $user->id, 'report_month' => $reportMonth->toDateString()],
                ['reminded_at' => now()]
            );

            $alreadySent = $user->notifications()
                ->where('type', MonthlyReportReminderNotification::class)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if (! $alreadySent) {
                $user->notify(new MonthlyReportReminderNotification(
                    $label,
                    $summary['total'],
                    $summary['completed'],
                    $summary['pending'] + $summary['in_progress'],
                ));
            }
        });

        $this->info('Monthly report reminders created.');

        return self::SUCCESS;
    }
}
