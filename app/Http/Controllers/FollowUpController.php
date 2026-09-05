<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $base = $user->activities()->with('category')->withOpenFollowUp();

        $overdue = (clone $base)->whereNotNull('follow_up_due_at')->where('follow_up_due_at', '<', now()->startOfDay())->get();
        $dueToday = (clone $base)->whereDate('follow_up_due_at', now()->toDateString())->get();
        $upcoming = (clone $base)->where('follow_up_due_at', '>', now()->endOfDay())->orderBy('follow_up_due_at')->limit(20)->get();
        $noDate = (clone $base)->whereNull('follow_up_due_at')->latest('activity_date')->limit(20)->get();

        return view('follow-ups.index', compact('overdue', 'dueToday', 'upcoming', 'noDate'));
    }

    public function complete(Request $request, \App\Models\Activity $activity)
    {
        $this->authorize('update', $activity);
        abort_unless($activity->follow_up_required, 422);

        $activity->update([
            'follow_up_status' => 'completed',
            'follow_up_completed_at' => now(),
        ]);

        return back()->with('success', 'Follow-up completed.');
    }
}
