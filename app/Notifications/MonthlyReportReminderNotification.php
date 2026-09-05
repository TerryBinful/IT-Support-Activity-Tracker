<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MonthlyReportReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $reportMonth,
        public int $total,
        public int $completed,
        public int $outstanding,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Monthly report reminder',
            'message' => "Your {$this->reportMonth} activity report is ready for review.",
            'total' => $this->total,
            'completed' => $this->completed,
            'outstanding' => $this->outstanding,
            'url' => route('reports.index', ['period' => 'previous_month']),
        ];
    }
}
