<?php

namespace App\Services\Activities;

use App\Models\ActivityTemplate;
use App\Models\User;

class CreateActivityFromTemplate
{
    public function __construct(private CreateActivity $createActivity) {}

    public function defaults(User $user, ActivityTemplate $template): array
    {
        abort_unless($template->user_id === $user->id, 403);

        return [
            'title' => $template->title,
            'description' => $template->description,
            'category_id' => $template->category_id,
            'priority' => $template->default_priority,
            'status' => $template->default_status,
            'activity_date' => now()->toDateString(),
            'follow_up_required' => $template->default_follow_up_required,
            'follow_up_action' => $template->default_follow_up_action,
            'follow_up_status' => $template->default_follow_up_required ? 'open' : null,
        ];
    }

    public function handle(User $user, ActivityTemplate $template, array $overrides = [])
    {
        return $this->createActivity->handle($user, array_merge($this->defaults($user, $template), $overrides));
    }
}
