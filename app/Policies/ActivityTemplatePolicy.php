<?php

namespace App\Policies;

use App\Models\ActivityTemplate;
use App\Models\User;

class ActivityTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ActivityTemplate $template): bool
    {
        return $template->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ActivityTemplate $template): bool
    {
        return $template->user_id === $user->id;
    }

    public function delete(User $user, ActivityTemplate $template): bool
    {
        return $template->user_id === $user->id;
    }
}
