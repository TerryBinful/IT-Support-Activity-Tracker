<?php

namespace App\Services\Recurring;

use App\Models\Activity;
use App\Models\RecurringActivity;
use App\Services\Activities\CreateActivity;
use App\Services\Activities\RecordActivityHistory;
use Carbon\Carbon;

class GenerateRecurringActivities
{
    public function __construct(
        private CreateActivity $createActivity,
        private RecordActivityHistory $history,
    ) {}

    public function handle(?Carbon $now = null): int
    {
        $now = $now ?? now();
        $generated = 0;

        RecurringActivity::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', $now);
            })
            ->with('user')
            ->each(function (RecurringActivity $recurring) use ($now, &$generated) {
                if ($this->alreadyGeneratedToday($recurring, $now)) {
                    return;
                }

                $activity = $this->createActivity->handle($recurring->user, [
                    'title' => $recurring->title,
                    'description' => $recurring->description,
                    'category_id' => $recurring->category_id,
                    'priority' => $recurring->priority,
                    'status' => 'completed',
                    'activity_date' => $now->toDateString(),
                    'recurring_activity_id' => $recurring->id,
                ]);

                $this->history->record($activity, $recurring->user, 'recurring_generated', null, null, [
                    'recurring_activity_id' => $recurring->id,
                ]);

                $recurring->update([
                    'last_generated_at' => $now,
                    'next_run_at' => $this->nextRunAt($recurring, $now),
                ]);

                $generated++;
            });

        return $generated;
    }

    private function alreadyGeneratedToday(RecurringActivity $recurring, Carbon $now): bool
    {
        if (! $recurring->last_generated_at) {
            return false;
        }

        return $recurring->last_generated_at->isSameDay($now);
    }

    public function nextRunAt(RecurringActivity $recurring, Carbon $from): Carbon
    {
        return match ($recurring->recurrence_type) {
            'daily' => $from->copy()->addDay()->startOfDay(),
            'weekly' => $from->copy()->addWeek()->startOfDay(),
            'monthly' => $from->copy()->addMonth()->startOfDay(),
            default => $from->copy()->addDay()->startOfDay(),
        };
    }
}
