<?php

namespace App\Http\Controllers;

use App\Exports\ActivitiesExport;
use App\Exports\PdfActivitiesExport;
use App\Services\Reports\ReportColumnRegistry;
use App\Services\Reports\ReportQueryBuilder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        private ReportColumnRegistry $columns,
        private ReportQueryBuilder $queryBuilder,
    ) {}

    public function index(Request $request)
    {
        $labels = $this->columns->labels();
        $default = $request->user()->reportPreferences()->where('is_default', true)->first();
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        if ($request->input('period') === 'previous_month') {
            $from = now()->subMonth()->startOfMonth()->toDateString();
            $to = now()->subMonth()->endOfMonth()->toDateString();
        }

        $selected = $this->columns->sanitize($this->list($request->input('columns', $default?->columns ?? ['activity_date', 'title', 'category', 'status', 'outcome'])));
        $order = $this->columns->sanitizeOrder($this->list($request->input('order_json', $default?->column_order ?? $selected)), $selected);
        $summary = $this->queryBuilder->summary($request->user(), $from, $to);
        $preview = $this->queryBuilder->forUser($request->user(), $from, $to)->limit(10)->get();
        $savedPreferences = $request->user()->reportPreferences()->latest()->get();

        return view('reports.index', [
            'columns' => $labels,
            'selected' => $selected,
            'order' => $order,
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'preview' => $preview,
            'savedPreferences' => $savedPreferences,
        ]);
    }

    public function savePreference(Request $request)
    {
        $labels = $this->columns->labels();
        $d = $request->validate([
            'name' => 'required|string|max:100',
            'columns' => 'required|array',
            'order' => 'required|array',
            'is_default' => 'nullable|boolean',
            'date_range_mode' => 'nullable|string|max:30',
        ]);

        $c = $this->columns->sanitize($d['columns']);
        $o = $this->columns->sanitizeOrder($d['order'], $c);

        if ($request->boolean('is_default')) {
            $request->user()->reportPreferences()->update(['is_default' => false]);
        }

        $request->user()->reportPreferences()->create([
            'name' => $d['name'],
            'columns' => $c,
            'column_order' => $o,
            'date_range_mode' => $d['date_range_mode'] ?? null,
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'Report preset saved.');
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['xlsx', 'csv', 'pdf'], true), 404);

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());
        $c = $this->columns->sanitize($this->list($request->input('columns', 'activity_date,title,category,status,outcome')));
        $o = $this->columns->sanitizeOrder($this->list($request->input('order', $c)), $c);
        $labels = $this->columns->labels();

        $export = new ActivitiesExport($request->user(), $from, $to, $o);

        if ($format === 'xlsx') {
            return Excel::download($export, 'it-activity-report.xlsx');
        }

        if ($format === 'csv') {
            return response()->streamDownload(fn () => print ($export->toCsv()), 'it-activity-report.csv', [
                'Content-Type' => 'text/csv',
            ]);
        }

        return app(PdfActivitiesExport::class)->download($request->user(), $from, $to, $o, $labels);
    }

    private function list($v): array
    {
        if (is_array($v)) {
            return array_values(array_filter($v));
        }

        if (is_string($v)) {
            $j = json_decode($v, true);
            if (is_array($j)) {
                return array_values(array_filter($j));
            }

            return array_values(array_filter(array_map('trim', explode(',', $v))));
        }

        return [];
    }
}
