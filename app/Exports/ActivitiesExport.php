<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActivitiesExport implements FromCollection, WithHeadings
{
    public function __construct(
        private User $user,
        private string $from,
        private string $to,
        private array $order,
    ) {}

    public function collection(): Enumerable
    {
        return $this->user->activities()
            ->with('category')
            ->whereBetween('activity_date', [$this->from, $this->to])
            ->orderBy('activity_date')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($activity) => collect($this->order)->map(fn ($column) => $this->value($activity, $column))->all());
    }

    public function headings(): array
    {
        return collect($this->order)->map(fn ($column) => str($column)->replace('_', ' ')->title()->toString())->all();
    }

    private function value($activity, string $column): mixed
    {
        return match ($column) {
            'activity_date' => $activity->activity_date?->format('Y-m-d'),
            'created_at' => $activity->created_at?->format('Y-m-d H:i:s'),
            'category' => $activity->category?->name,
            'started_at' => $activity->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $activity->completed_at?->format('Y-m-d H:i:s'),
            'follow_up_required' => $activity->follow_up_required ? 'Yes' : 'No',
            'follow_up_due_at' => $activity->follow_up_due_at?->format('Y-m-d'),
            default => $activity->{$column} ?? null,
        };
    }

    public function toCsv(): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $this->headings());

        foreach ($this->collection() as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
