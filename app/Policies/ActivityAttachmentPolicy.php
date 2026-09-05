<?php

namespace App\Policies;

use App\Models\ActivityAttachment;
use App\Models\User;

class ActivityAttachmentPolicy
{
    public function view(User $user, ActivityAttachment $attachment): bool
    {
        return $attachment->user_id === $user->id;
    }

    public function delete(User $user, ActivityAttachment $attachment): bool
    {
        return $attachment->user_id === $user->id;
    }
}
