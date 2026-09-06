<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'recurring_activity_id', 'title', 'description',
        'priority', 'status', 'activity_date', 'started_at', 'completed_at', 'duration_minutes',
        'outcome', 'blockers', 'follow_up_required', 'follow_up_action',
        'follow_up_due_at', 'follow_up_status', 'follow_up_completed_at',
        'reference_number', 'evidence_url', 'quick_log_key',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'follow_up_required' => 'boolean',
            'follow_up_due_at' => 'datetime',
            'follow_up_completed_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recurringActivity(): BelongsTo
    {
        return $this->belongsTo(RecurringActivity::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ActivityAttachment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ActivityHistory::class)->latest('created_at');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeWithOpenFollowUp($query)
    {
        return $query->where('follow_up_required', true)
            ->where(function ($q) {
                $q->whereNull('follow_up_status')->orWhere('follow_up_status', 'open');
            });
    }

    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    public function formattedDuration(): ?string
    {
        if ($this->duration_minutes === null) {
            return null;
        }

        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
