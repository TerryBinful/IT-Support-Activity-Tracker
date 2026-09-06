<?php

namespace App\Services\Reports;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportQueryBuilder
{
    public function forUser(User $user, string $from, string $to, array $filters = []): HasMany
    {
        $query = $user->activities()
            ->with('category')
            ->whereBetween('activity_date', [$from, $to]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->orderBy('activity_date')->orderBy('created_at');
    }

    public function summary(User $user, string $from, string $to, array $filters = []): array
    {
        $base = $this->forUser($user, $from, $to, $filters);
        $activities = (clone $base)->get();

        return [
            'total' => $activities->count(),
            'completed' => $activities->where('status', 'completed')->count(),
            'in_progress' => $activities->where('status', 'in_progress')->count(),
            'pending' => $activities->whereIn('status', ['pending', 'on_hold'])->count(),
            'total_minutes' => $activities->sum('duration_minutes'),
            'by_category' => $activities->groupBy(fn ($a) => $a->category?->name ?? 'Uncategorised')
                ->map->count()
                ->sortDesc(),
            'follow_ups_open' => $activities->filter(fn ($a) => $a->follow_up_required && ($a->follow_up_status === 'open' || $a->follow_up_status === null))->count(),
        ];
    }
}
