<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RecurringActivity;
use App\Services\Recurring\GenerateRecurringActivities;
use Illuminate\Http\Request;

class RecurringActivityController extends Controller
{
    public function index(Request $request)
    {
        $recurring = $request->user()->recurringActivities()->with('category')->latest()->paginate(20);

        return view('recurring.index', compact('recurring'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('recurring.create', compact('categories'));
    }

    public function store(Request $request, GenerateRecurringActivities $generator)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['next_run_at'] = $generator->initialRunAt($data['recurrence_type'], $data['recurrence_day'] ?? null, now());

        RecurringActivity::create($data);

        return redirect()->route('recurring.index')->with('success', 'Recurring activity saved.');
    }

    public function edit(Request $request, RecurringActivity $recurring)
    {
        abort_unless($recurring->user_id === $request->user()->id, 403);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('recurring.edit', compact('recurring', 'categories'));
    }

    public function update(Request $request, RecurringActivity $recurring)
    {
        abort_unless($recurring->user_id === $request->user()->id, 403);
        $data = $this->validated($request);
        $data['next_run_at'] = app(GenerateRecurringActivities::class)
            ->initialRunAt($data['recurrence_type'], $data['recurrence_day'] ?? null, now());
        $recurring->update($data);

        return redirect()->route('recurring.index')->with('success', 'Recurring activity updated.');
    }

    public function destroy(Request $request, RecurringActivity $recurring)
    {
        abort_unless($recurring->user_id === $request->user()->id, 403);
        $recurring->delete();

        return redirect()->route('recurring.index')->with('success', 'Recurring activity deleted.');
    }

    public function toggle(Request $request, RecurringActivity $recurring)
    {
        abort_unless($recurring->user_id === $request->user()->id, 403);
        $recurring->update(['is_active' => ! $recurring->is_active]);

        return back()->with('success', $recurring->is_active ? 'Recurrence resumed.' : 'Recurrence paused.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'priority' => 'required|in:low,medium,high,critical',
            'recurrence_type' => 'required|in:daily,weekly,monthly',
            'recurrence_day' => 'nullable|integer|min:0|max:31',
            'is_active' => 'nullable|boolean',
        ]) + [
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
