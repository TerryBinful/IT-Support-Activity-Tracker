<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Models\User;
use InvalidArgumentException;

class QuickLogActivity
{
    public function __construct(private CreateActivity $createActivity) {}

    public function handle(User $user, array $data): Activity
    {
        return $this->createActivity->handle($user, [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'activity_date' => $data['activity_date'] ?? now()->toDateString(),
            'priority' => $data['priority'] ?? 'medium',
            'status' => $data['status'] ?? 'completed',
        ]);
    }
}
