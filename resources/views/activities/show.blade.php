@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="text-sm text-slate-500">{{ $activity->activity_date?->format('l, d F Y') }}</p>
            <h1 class="text-3xl font-bold">{{ $activity->title }}</h1>
            <div class="mt-2 flex flex-wrap gap-2 text-sm text-slate-500">
                <span>{{ $activity->category?->name ?? 'Uncategorised' }}</span>
                <span>{{ str($activity->priority)->title() }} priority</span>
                <span>{{ str($activity->status)->replace('_', ' ')->title() }}</span>
                @if ($activity->recurring_activity_id)
                    <span class="rounded bg-indigo-100 px-2 py-0.5 text-indigo-900">Recurring</span>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($activity->status === 'in_progress')
                <form method="POST" action="{{ route('activities.complete', $activity) }}">@csrf<button class="btn">Complete Task</button></form>
            @elseif (! in_array($activity->status, ['completed', 'cancelled']))
                <form method="POST" action="{{ route('activities.start', $activity) }}">@csrf<button class="btn-secondary">Start Task</button></form>
            @endif
            <a href="{{ route('activities.edit', $activity) }}" class="btn-secondary">Edit</a>
        </div>
    </div>

    @if ($activity->status === 'in_progress' && $activity->started_at)
        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
            Started {{ $activity->started_at->format('H:i') }} · Running for {{ $activity->started_at->diffForHumans(now(), true) }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if ($activity->description)
                <section class="rounded-xl border bg-white p-5">
                    <h2 class="font-semibold">Description</h2>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $activity->description }}</p>
                </section>
            @endif

            <section class="rounded-xl border bg-white p-5">
                <h2 class="font-semibold">Timing</h2>
                <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">Logged at</dt><dd>{{ $activity->created_at?->format('d M Y H:i') }}</dd></div>
                    <div><dt class="text-slate-500">Started</dt><dd>{{ $activity->started_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Completed</dt><dd>{{ $activity->completed_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Duration</dt><dd>{{ $activity->formattedDuration() ?? '—' }}</dd></div>
                </dl>
            </section>

            @if ($activity->outcome || $activity->blockers)
                <section class="rounded-xl border bg-white p-5">
                    <h2 class="font-semibold">Outcome</h2>
                    @if ($activity->outcome)<p class="mt-2 text-sm">{{ $activity->outcome }}</p>@endif
                    @if ($activity->blockers)
                        <h3 class="mt-4 font-medium">Blockers</h3>
                        <p class="mt-1 text-sm">{{ $activity->blockers }}</p>
                    @endif
                </section>
            @endif

            @if ($activity->follow_up_required)
                <section class="rounded-xl border bg-white p-5">
                    <h2 class="font-semibold">Follow-up</h2>
                    <p class="mt-2 text-sm">{{ $activity->follow_up_action ?? 'No action recorded.' }}</p>
                    @if ($activity->follow_up_due_at)
                        <p class="mt-2 text-sm text-slate-500">Due {{ $activity->follow_up_due_at->format('d M Y') }}</p>
                    @endif
                </section>
            @endif

            <section class="rounded-xl border bg-white p-5">
                <h2 class="font-semibold">Evidence</h2>
                @if ($activity->evidence_url)
                    <p class="mt-2 text-sm"><a href="{{ $activity->evidence_url }}" class="underline" target="_blank" rel="noopener">External link</a></p>
                @endif
                <ul class="mt-3 space-y-2">
                    @forelse ($activity->attachments as $attachment)
                        <li class="flex items-center justify-between rounded-lg border px-3 py-2 text-sm">
                            <span>📎 {{ $attachment->original_name }}</span>
                            <div class="flex gap-2">
                                <a href="{{ route('activities.attachments.download', [$activity, $attachment]) }}" class="font-semibold underline">Download</a>
                                <form method="POST" action="{{ route('activities.attachments.destroy', [$activity, $attachment]) }}" onsubmit="return confirm('Remove this attachment?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">Remove</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">No files attached.</li>
                    @endforelse
                </ul>
                <form method="POST" action="{{ route('activities.attachments.store', $activity) }}" enctype="multipart/form-data" class="mt-4" data-evidence-form>
                    @csrf
                    <label for="evidence-files" class="group block cursor-pointer rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition hover:border-slate-500 hover:bg-white focus-within:border-slate-500 focus-within:ring-2 focus-within:ring-slate-200">
                        <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-white text-2xl shadow-sm" aria-hidden="true">+</span>
                        <span class="mt-3 block font-semibold text-slate-900">Add evidence files</span>
                        <span class="mt-1 block text-sm text-slate-500">Choose screenshots, documents, exports, or diagnostic files</span>
                        <span class="mt-2 block text-xs text-slate-400">Multiple files allowed · 10 MB maximum per file</span>
                        <input id="evidence-files" type="file" name="attachments[]" multiple class="sr-only" accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.csv,.doc,.docx,.xls,.xlsx" data-evidence-input>
                    </label>
                    <div class="mt-3 hidden rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700" data-evidence-selection aria-live="polite"></div>
                    <button type="submit" class="btn mt-3" data-evidence-submit>Upload evidence</button>
                </form>
            </section>

            <section class="rounded-xl border bg-white p-5">
                <h2 class="font-semibold">History</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($activity->histories as $entry)
                        <li class="flex gap-3">
                            <span class="shrink-0 text-slate-500">{{ $entry->created_at?->format('H:i') }}</span>
                            <span>{{ str($entry->event_type)->replace('_', ' ')->title() }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">No history yet.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <aside class="space-y-4">
            @if ($activity->reference_number)
                <div class="rounded-xl border bg-white p-4 text-sm">
                    <div class="font-semibold">Reference</div>
                    <div class="mt-1">{{ $activity->reference_number }}</div>
                </div>
            @endif
            @if ($activity->tags->isNotEmpty())
                <div class="rounded-xl border bg-white p-4">
                    <div class="font-semibold">Tags</div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($activity->tags as $tag)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>

    <script>
        (() => {
            const form = document.querySelector('[data-evidence-form]');
            const input = document.querySelector('[data-evidence-input]');
            const selection = document.querySelector('[data-evidence-selection]');

            if (!form || !input || !selection) return;

            input.addEventListener('change', () => {
                const files = Array.from(input.files || []);

                if (files.length === 0) {
                    selection.classList.add('hidden');
                    selection.textContent = '';
                    return;
                }

                selection.classList.remove('hidden');
                selection.textContent = `${files.length} file${files.length === 1 ? '' : 's'} selected: ${files.map((file) => file.name).join(', ')}`;
            });
        })();
    </script>
@endsection
