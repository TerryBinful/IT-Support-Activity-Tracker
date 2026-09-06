<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Arr;

class CreateActivity
{
    public function __construct(private RecordActivityHistory $history) {}

    public function handle(User $user, array $data): Activity
    {
        $data['user_id'] = $user->id;
        $data = $this->applyFollowUpDefaults($data);
        $this->calculateDuration($data);

        $attributes = Arr::only($data, (new Activity)->getFillable());
        if (isset($attributes['quick_log_key'])) {
            $activity = Activity::firstOrCreate(
                ['user_id' => $user->id, 'quick_log_key' => $attributes['quick_log_key']],
                $attributes,
            );
        } elseif (isset($attributes['recurring_activity_id'])) {
            $activity = Activity::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'recurring_activity_id' => $attributes['recurring_activity_id'],
                    'activity_date' => $attributes['activity_date'],
                ],
                $attributes,
            );
        } else {
            $activity = Activity::create($attributes);
        }

        if ($activity->wasRecentlyCreated) {
            $this->history->record($activity, $user, 'created', null, [
                'title' => $activity->title,
                'status' => $activity->status,
            ]);
        }

        return $activity;
    }

    private function applyFollowUpDefaults(array $data): array
    {
        if (! empty($data['follow_up_required']) && empty($data['follow_up_status'])) {
            $data['follow_up_status'] = 'open';
        }

        return $data;
    }

    private function calculateDuration(array &$data): void
    {
        if (! empty($data['started_at']) && ! empty($data['completed_at'])) {
            $data['duration_minutes'] = max(0, (int) round(
                (strtotime($data['completed_at']) - strtotime($data['started_at'])) / 60
            ));
        }
    }
}
