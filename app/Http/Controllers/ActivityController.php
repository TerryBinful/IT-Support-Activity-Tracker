<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityAttachment;
use App\Models\Category;
use App\Models\Tag;
use App\Services\Activities\CompleteActivity;
use App\Services\Activities\CreateActivity;
use App\Services\Activities\QuickLogActivity;
use App\Services\Activities\RecordActivityHistory;
use App\Services\Activities\StartActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    public function __construct(
        private CreateActivity $createActivity,
        private QuickLogActivity $quickLogActivity,
        private StartActivity $startActivity,
        private CompleteActivity $completeActivity,
        private RecordActivityHistory $history,
    ) {}

    public function index(Request $request)
    {
        $query = $request->user()->activities()->with('category')->latest('activity_date')->latest('created_at');

        if ($request->filled('view')) {
            match ($request->view) {
                'today' => $query->whereDate('activity_date', now()->toDateString()),
                'active' => $query->where('status', 'in_progress'),
                'follow-ups' => $query->withOpenFollowUp(),
                default => null,
            };
        }

        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'ilike', $term)
                    ->orWhere('description', 'ilike', $term)
                    ->orWhere('outcome', 'ilike', $term)
                    ->orWhere('blockers', 'ilike', $term)
                    ->orWhere('reference_number', 'ilike', $term)
                    ->orWhere('follow_up_action', 'ilike', $term);
            });
        }

        foreach (['status', 'priority'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->$field);
            }
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('date')) {
            $query->whereDate('activity_date', $request->date);
        }

        if ($request->filled('from')) {
            $query->whereDate('activity_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('activity_date', '<=', $request->to);
        }

        $activities = $query->paginate(20)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('activities.index', compact('activities', 'categories'));
    }

    public function create(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $templates = $request->user()->activityTemplates()->where('is_active', true)->orderBy('title')->get();
        $defaults = [];

        if ($request->filled('template')) {
            $template = $request->user()->activityTemplates()->findOrFail($request->template);
            $defaults = app(\App\Services\Activities\CreateActivityFromTemplate::class)->defaults($request->user(), $template);
        }

        return view('activities.create', compact('categories', 'templates', 'defaults'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['follow_up_required'] = $request->boolean('follow_up_required');

        $activity = $this->createActivity->handle($request->user(), $data);
        $this->syncTags($request, $activity);

        return redirect()->route('activities.show', $activity)->with('success', 'Activity saved.');
    }

    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $this->quickLogActivity->handle($request->user(), $data);

        return redirect()->back()->with('success', 'Activity saved.');
    }

    public function show(Request $request, Activity $activity)
    {
        $this->authorize('view', $activity);

        $activity->load(['category', 'attachments', 'histories', 'tags', 'recurringActivity']);

        return view('activities.show', compact('activity'));
    }

    public function edit(Request $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $activity->load('tags');
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('activities.edit', compact('activity', 'categories'));
    }

    public function update(Request $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $old = $activity->only(['title', 'status', 'priority', 'outcome', 'blockers']);
        $data = $this->validated($request);
        $data['follow_up_required'] = $request->boolean('follow_up_required');

        if ($data['follow_up_required'] && empty($data['follow_up_status'])) {
            $data['follow_up_status'] = 'open';
        }

        if (! $data['follow_up_required']) {
            $data['follow_up_status'] = null;
            $data['follow_up_due_at'] = null;
        }

        if (! empty($data['started_at']) && ! empty($data['completed_at'])) {
            $data['duration_minutes'] = max(0, (int) round(
                (strtotime($data['completed_at']) - strtotime($data['started_at'])) / 60
            ));
        }

        $activity->update($data);
        $this->syncTags($request, $activity);
        $this->history->record($activity, $request->user(), 'updated', $old, $activity->only(array_keys($old)));

        return redirect()->route('activities.show', $activity)->with('success', 'Activity updated.');
    }

    public function destroy(Request $request, Activity $activity)
    {
        $this->authorize('delete', $activity);

        foreach ($activity->attachments as $attachment) {
            Storage::disk('local')->delete($attachment->stored_path);
        }

        $activity->delete();

        return redirect()->route('activities.index')->with('success', 'Activity deleted.');
    }

    public function start(Request $request, Activity $activity)
    {
        $this->authorize('start', $activity);

        try {
            $this->startActivity->handle($activity, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('success', 'Task started.');
    }

    public function complete(Request $request, Activity $activity)
    {
        $this->authorize('complete', $activity);

        try {
            $this->completeActivity->handle($activity, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('success', 'Task completed.');
    }

    public function storeAttachment(Request $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $request->validate([
            'attachments' => 'required|array|min:1',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,txt,csv,doc,docx,xls,xlsx',
        ]);

        foreach ($request->file('attachments') as $file) {
            $path = $file->store('activity-evidence/'.$activity->id, 'local');
            $attachment = $activity->attachments()->create([
                'user_id' => $request->user()->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $this->history->record($activity, $request->user(), 'attachment_added', null, null, [
                'attachment_id' => $attachment->id,
                'original_name' => $attachment->original_name,
            ]);
        }

        return back()->with('success', 'Evidence uploaded.');
    }

    public function downloadAttachment(Request $request, Activity $activity, ActivityAttachment $attachment)
    {
        $this->authorize('view', $activity);
        abort_unless($attachment->activity_id === $activity->id, 404);
        $this->authorize('view', $attachment);

        abort_unless(Storage::disk('local')->exists($attachment->stored_path), 404);

        return Storage::disk('local')->download($attachment->stored_path, $attachment->original_name);
    }

    public function destroyAttachment(Request $request, Activity $activity, ActivityAttachment $attachment)
    {
        $this->authorize('update', $activity);
        abort_unless($attachment->activity_id === $activity->id, 404);
        $this->authorize('delete', $attachment);

        Storage::disk('local')->delete($attachment->stored_path);
        $this->history->record($activity, $request->user(), 'attachment_removed', null, null, [
            'original_name' => $attachment->original_name,
        ]);
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:completed,in_progress,pending,on_hold,cancelled',
            'activity_date' => 'required|date',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after_or_equal:started_at',
            'outcome' => 'nullable|string',
            'blockers' => 'nullable|string',
            'follow_up_required' => 'nullable|boolean',
            'follow_up_action' => 'nullable|string',
            'follow_up_due_at' => 'nullable|date',
            'follow_up_status' => ['nullable', Rule::in(['open', 'completed', 'cancelled'])],
            'reference_number' => 'nullable|string|max:255',
            'evidence_url' => 'nullable|url|max:2048',
            'tags' => 'nullable|string',
        ]);
    }

    private function syncTags(Request $request, Activity $activity): void
    {
        if (! $request->has('tags')) {
            return;
        }

        $names = collect(explode(',', (string) $request->tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->take(10);

        $tagIds = $names->map(function ($name) use ($request) {
            return Tag::firstOrCreate([
                'user_id' => $request->user()->id,
                'name' => Str::limit($name, 50, ''),
            ])->id;
        });

        $activity->tags()->sync($tagIds);
    }
}
