@php
    $activity = $activity ?? null;
    $defaults = $defaults ?? [];
    $value = fn (string $field, mixed $default = null) => old($field, $defaults[$field] ?? $activity?->{$field} ?? $default);
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="title" class="label">Title</label>
        <input id="title" name="title" type="text" value="{{ $value('title') }}" required class="input">
    </div>

    <div class="md:col-span-2">
        <label for="description" class="label">Description</label>
        <textarea id="description" name="description" rows="3" class="input">{{ $value('description') }}</textarea>
    </div>

    <div>
        <label for="category_id" class="label">Category</label>
        <select id="category_id" name="category_id" class="input">
            <option value="">Uncategorised</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $value('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="activity_date" class="label">Activity date</label>
        <input id="activity_date" name="activity_date" type="date" value="{{ $value('activity_date', now()->toDateString()) }}" required class="input">
    </div>

    <div>
        <label for="priority" class="label">Priority</label>
        <select id="priority" name="priority" required class="input">
            @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                <option value="{{ $priority }}" @selected($value('priority', 'medium') === $priority)>{{ str($priority)->title() }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="status" class="label">Status</label>
        <select id="status" name="status" required class="input">
            @foreach (['completed', 'in_progress', 'pending', 'on_hold', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected($value('status', 'completed') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
            @endforeach
        </select>
    </div>

    <details class="md:col-span-2 rounded-lg border p-4">
        <summary class="cursor-pointer font-medium">Timing</summary>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label for="started_at" class="label">Start time</label>
                <input id="started_at" name="started_at" type="datetime-local" value="{{ old('started_at', $activity?->started_at?->format('Y-m-d\TH:i')) }}" class="input">
            </div>
            <div>
                <label for="completed_at" class="label">End time</label>
                <input id="completed_at" name="completed_at" type="datetime-local" value="{{ old('completed_at', $activity?->completed_at?->format('Y-m-d\TH:i')) }}" class="input">
            </div>
        </div>
    </details>

    <details class="md:col-span-2 rounded-lg border p-4">
        <summary class="cursor-pointer font-medium">Outcome & blockers</summary>
        <div class="mt-4 space-y-4">
            <div>
                <label for="outcome" class="label">Outcome</label>
                <textarea id="outcome" name="outcome" rows="2" class="input">{{ $value('outcome') }}</textarea>
            </div>
            <div>
                <label for="blockers" class="label">Blockers</label>
                <textarea id="blockers" name="blockers" rows="2" class="input">{{ $value('blockers') }}</textarea>
            </div>
        </div>
    </details>

    <details class="md:col-span-2 rounded-lg border p-4">
        <summary class="cursor-pointer font-medium">Follow-up</summary>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="follow_up_required" value="1" @checked(old('follow_up_required', $activity?->follow_up_required ?? false)) class="rounded border-slate-300">
                Follow-up required
            </label>
            <div>
                <label for="follow_up_due_at" class="label">Follow-up due</label>
                <input id="follow_up_due_at" name="follow_up_due_at" type="date" value="{{ old('follow_up_due_at', $activity?->follow_up_due_at?->format('Y-m-d')) }}" class="input">
            </div>
            <div class="md:col-span-2">
                <label for="follow_up_action" class="label">Next action</label>
                <textarea id="follow_up_action" name="follow_up_action" rows="2" class="input">{{ $value('follow_up_action') }}</textarea>
            </div>
        </div>
    </details>

    <details class="md:col-span-2 rounded-lg border p-4">
        <summary class="cursor-pointer font-medium">Evidence & reference</summary>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label for="reference_number" class="label">Reference number</label>
                <input id="reference_number" name="reference_number" type="text" value="{{ $value('reference_number') }}" class="input">
            </div>
            <div>
                <label for="evidence_url" class="label">Evidence URL</label>
                <input id="evidence_url" name="evidence_url" type="url" value="{{ $value('evidence_url') }}" class="input">
            </div>
            <div class="md:col-span-2">
                <label for="tags" class="label">Tags (comma separated)</label>
                <input id="tags" name="tags" type="text" value="{{ old('tags', $activity?->tags->pluck('name')->join(', ')) }}" class="input" placeholder="Cisco, VLAN, Backup">
            </div>
        </div>
    </details>
</div>
