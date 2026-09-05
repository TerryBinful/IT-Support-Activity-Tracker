<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringActivity extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'description', 'priority',
        'recurrence_type', 'recurrence_day', 'next_run_at', 'last_generated_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'next_run_at' => 'datetime',
            'last_generated_at' => 'datetime',
            'is_active' => 'boolean',
            'recurrence_day' => 'integer',
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

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
