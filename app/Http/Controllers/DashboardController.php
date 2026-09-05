<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $todayDate = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $today = $user->activities()->with('category')
            ->whereDate('activity_date', $todayDate)
            ->latest('created_at')
            ->get();

        $monthQuery = $user->activities()->whereBetween('activity_date', [$monthStart, $monthEnd]);
        $monthActivities = (clone $monthQuery)->with('category')->get();
        $activeTasks = $user->activities()->active()->with('category')->latest('started_at')->get();

        $stats = [
            'today' => $today->count(),
            'month' => $monthActivities->count(),
            'completed' => $monthActivities->where('status', 'completed')->count(),
            'open' => $monthActivities->whereIn('status', ['in_progress', 'pending', 'on_hold'])->count(),
            'in_progress' => $monthActivities->where('status', 'in_progress')->count(),
            'follow_ups_due' => $user->activities()->withOpenFollowUp()
                ->whereNotNull('follow_up_due_at')
                ->where('follow_up_due_at', '<=', now()->endOfDay())
                ->count(),
            'active_tasks' => $activeTasks->count(),
            'total_minutes' => $monthActivities->sum('duration_minutes'),
            'completion_rate' => $monthActivities->count() > 0
                ? round(($monthActivities->where('status', 'completed')->count() / $monthActivities->count()) * 100)
                : 0,
        ];

        $byCategory = $monthActivities->groupBy(fn ($a) => $a->category?->name ?? 'Uncategorised')->map->count()->sortDesc();
        $byStatus = $monthActivities->groupBy('status')->map->count();
        $volume = $user->activities()
            ->where('activity_date', '>=', now()->subDays(13)->toDateString())
            ->selectRaw('activity_date, COUNT(*) as total')
            ->groupBy('activity_date')
            ->orderBy('activity_date')
            ->pluck('total', 'activity_date');

        $longestTask = $monthActivities->where('duration_minutes', '>', 0)->sortByDesc('duration_minutes')->first();
        $topCategory = $byCategory->keys()->first();
        $overdueFollowUps = $user->activities()->withOpenFollowUp()
            ->whereNotNull('follow_up_due_at')
            ->where('follow_up_due_at', '<', now()->startOfDay())
            ->count();

        $reminder = $user->reportReminders()
            ->whereDate('report_month', now()->startOfMonth())
            ->whereNull('acknowledged_at')
            ->latest()
            ->first();

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $unreadNotifications = $user->unreadNotifications()->count();

        return view('dashboard', compact(
            'today', 'stats', 'reminder', 'activeTasks', 'byCategory', 'byStatus',
            'volume', 'longestTask', 'topCategory', 'overdueFollowUps', 'categories', 'unreadNotifications'
        ));
    }

    public function dismissReminder(Request $request)
    {
        $reminder = $request->user()->reportReminders()
            ->whereNull('acknowledged_at')
            ->latest()
            ->firstOrFail();

        $reminder->update(['acknowledged_at' => now()]);

        return back()->with('success', 'Reminder dismissed.');
    }
}
