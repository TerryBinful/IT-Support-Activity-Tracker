<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Models\User;
use InvalidArgumentException;

class StartActivity
{
    public function __construct(private RecordActivityHistory $history) {}

    public function handle(Activity $activity, User $user): Activity
    {
        if ($activity->status === 'in_progress') {
            throw new InvalidArgumentException('This task is already in progress.');
        }

        if ($activity->status === 'completed') {
            throw new InvalidArgumentException('Completed tasks must be reopened before starting again.');
        }

        if (in_array($activity->status, ['cancelled'], true)) {
            throw new InvalidArgumentException('Cancelled tasks cannot be started.');
        }

        $old = ['status' => $activity->status, 'started_at' => $activity->started_at?->toIso8601String()];

        $activity->update([
            'status' => 'in_progress',
            'started_at' => $activity->started_at ?? now(),
            'completed_at' => null,
            'duration_minutes' => null,
        ]);

        $this->history->record($activity, $user, 'started', $old, [
            'status' => $activity->status,
            'started_at' => $activity->started_at?->toIso8601String(),
        ]);

        return $activity->fresh();
    }
}
