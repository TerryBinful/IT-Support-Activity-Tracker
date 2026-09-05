<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Models\User;
use InvalidArgumentException;

class CompleteActivity
{
    public function __construct(private RecordActivityHistory $history) {}

    public function handle(Activity $activity, User $user): Activity
    {
        if ($activity->status === 'completed') {
            throw new InvalidArgumentException('This task is already completed.');
        }

        $old = [
            'status' => $activity->status,
            'completed_at' => $activity->completed_at?->toIso8601String(),
            'duration_minutes' => $activity->duration_minutes,
        ];

        $completedAt = now();
        $startedAt = $activity->started_at;
        $duration = null;

        if ($startedAt) {
            $duration = max(0, (int) round($startedAt->diffInMinutes($completedAt)));
        }

        $activity->update([
            'status' => 'completed',
            'completed_at' => $completedAt,
            'duration_minutes' => $duration,
        ]);

        $this->history->record($activity, $user, 'completed', $old, [
            'status' => $activity->status,
            'completed_at' => $activity->completed_at?->toIso8601String(),
            'duration_minutes' => $activity->duration_minutes,
        ]);

        return $activity->fresh();
    }
}
