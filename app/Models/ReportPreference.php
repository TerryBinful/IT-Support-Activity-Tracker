<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportPreference extends Model
{
    protected $fillable = [
        'user_id', 'name', 'columns', 'column_order', 'filters',
        'sort_column', 'sort_direction', 'date_range_mode', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'columns' => 'array',
            'column_order' => 'array',
            'filters' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
