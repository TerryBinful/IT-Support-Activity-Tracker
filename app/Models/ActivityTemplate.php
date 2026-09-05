<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityTemplate extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'description',
        'default_priority', 'default_status', 'default_follow_up_required',
        'default_follow_up_action', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_follow_up_required' => 'boolean',
            'is_active' => 'boolean',
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
}
