<?php

namespace App\Services\Reports;

class ReportColumnRegistry
{
    public function labels(): array
    {
        return [
            'activity_date' => 'Date',
            'created_at' => 'Logged At',
            'title' => 'Activity',
            'category' => 'Category',
            'description' => 'Description',
            'priority' => 'Priority',
            'status' => 'Status',
            'started_at' => 'Start Time',
            'completed_at' => 'End Time',
            'duration_minutes' => 'Duration (min)',
            'outcome' => 'Outcome',
            'blockers' => 'Blockers',
            'follow_up_required' => 'Follow-up',
            'follow_up_action' => 'Next Action',
            'follow_up_due_at' => 'Follow-up Due',
            'reference_number' => 'Reference',
            'evidence_url' => 'Evidence URL',
        ];
    }

    public function keys(): array
    {
        return array_keys($this->labels());
    }

    public function sanitize(array $columns): array
    {
        return array_values(array_intersect($columns, $this->keys()));
    }

    public function sanitizeOrder(array $order, array $selected): array
    {
        $order = array_values(array_filter($order, fn ($key) => in_array($key, $selected, true)));

        foreach ($selected as $key) {
            if (! in_array($key, $order, true)) {
                $order[] = $key;
            }
        }

        return $order;
    }
}
