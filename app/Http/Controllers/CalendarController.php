<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $counts = $request->user()->activities()
            ->whereBetween('activity_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('activity_date, COUNT(*) as total')
            ->groupBy('activity_date')
            ->pluck('total', 'activity_date');

        $selectedDate = $request->input('date', now()->toDateString());
        $dayActivities = $request->user()->activities()
            ->with('category')
            ->whereDate('activity_date', $selectedDate)
            ->latest('created_at')
            ->get();

        return view('calendar.index', compact('start', 'counts', 'selectedDate', 'dayActivities', 'month'));
    }
}
