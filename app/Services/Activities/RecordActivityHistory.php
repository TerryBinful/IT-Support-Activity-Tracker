<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Models\ActivityHistory;
use App\Models\User;

class RecordActivityHistory
{
    public function record(
        Activity $activity,
        User $user,
        string $eventType,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
    ): ActivityHistory {
        return ActivityHistory::create([
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'event_type' => $eventType,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
