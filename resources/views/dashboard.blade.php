@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm text-slate-500">{{ now()->format('l, d F Y') }}</p>
            <h1 class="text-3xl font-bold">Dashboard</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('activities.create') }}" class="btn-secondary">+ New Activity</a>
            <a href="{{ route('activities.index', ['view' => 'active']) }}" class="btn-secondary">Active Tasks</a>
        </div>
    </div>

    @if ($reminder)
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5">
            <div class="font-semibold">Monthly report reminder</div>
            <p class="mt-1 text-sm text-amber-900">It's the last Friday. Your monthly activity report is ready to review.</p>
            <div class="mt-3 flex flex-wrap gap-3">
                <a href="{{ route('reports.index', ['period' => 'previous_month']) }}" class="font-semibold underline">Review report →</a>
                <form method="POST" action="{{ route('reminders.dismiss') }}">
                    @csrf
                    <button type="submit" class="text-sm text-amber-900 underline">Dismiss</button>
                </form>
            </div>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            @include('partials.quick_log', ['categories' => $categories])

            @if ($activeTasks->isNotEmpty())
                <section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <h2 class="font-semibold text-blue-900">Active tasks</h2>
                    <div class="mt-3 space-y-3">
                        @foreach ($activeTasks as $task)
                            <div class="rounded-lg border border-blue-100 bg-white p-4">
                                <div class="font-medium">{{ $task->title }}</div>
                                <div class="mt-1 text-sm text-slate-500">
                                    Started {{ $task->started_at?->format('H:i') ?? '—' }}
                                    @if ($task->started_at)
                                        · Running for {{ $task->started_at->diffForHumans(now(), true) }}
                                    @endif
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('activities.complete', $task) }}">
                                        @csrf
                                        <button type="submit" class="btn">Complete Task</button>
                                    </form>
                                    <a href="{{ route('activities.show', $task) }}" class="btn-secondary">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="overflow-hidden rounded-xl border bg-white">
                <div class="flex items-center justify-between border-b p-5">
                    <h2 class="font-semibold">Today's activities</h2>
                    <a href="{{ route('activities.index', ['view' => 'today']) }}" class="text-sm font-semibold">View all</a>
                </div>
                <div class="divide-y">
                    @forelse ($today as $activity)
                        <div class="flex items-center justify-between p-5">
                            <div>
                                <a href="{{ route('activities.show', $activity) }}" class="font-medium hover:underline">{{ $activity->title }}</a>
                                <div class="text-sm text-slate-500">{{ $activity->category?->name ?? 'Uncategorised' }} · {{ str($activity->status)->replace('_', ' ')->title() }}</div>
                            </div>
                            <div class="text-sm text-slate-500">{{ $activity->created_at?->format('H:i') }}</div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500">No activities logged today.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                @foreach ([
                    ['Today', $stats['today']],
                    ['This month', $stats['month']],
                    ['Completed', $stats['completed']],
                    ['In progress', $stats['in_progress']],
                    ['Follow-ups due', $stats['follow_ups_due']],
                    ['Time logged (min)', $stats['total_minutes']],
                ] as [$label, $value])
                    <div class="rounded-xl border bg-white p-4">
                        <div class="text-sm text-slate-500">{{ $label }}</div>
                        <div class="mt-1 text-2xl font-bold">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            <div class="rounded-xl border bg-white p-4">
                <div class="text-sm text-slate-500">Completion rate</div>
                <div class="mt-1 text-2xl font-bold">{{ $stats['completion_rate'] }}%</div>
            </div>

            @if ($topCategory)
                <div class="rounded-xl border bg-white p-4 text-sm">
                    <div class="font-semibold">Most active category</div>
                    <div class="mt-1 text-slate-600">{{ $topCategory }} ({{ $byCategory->first() }} activities)</div>
                </div>
            @endif

            @if ($longestTask)
                <div class="rounded-xl border bg-white p-4 text-sm">
                    <div class="font-semibold">Longest completed task</div>
                    <div class="mt-1 text-slate-600">{{ $longestTask->title }} · {{ $longestTask->formattedDuration() }}</div>
                </div>
            @endif

            @if ($overdueFollowUps > 0)
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm">
                    <div class="font-semibold text-red-900">{{ $overdueFollowUps }} overdue follow-up(s)</div>
                    <a href="{{ route('follow-ups.index') }}" class="mt-2 inline-block font-semibold underline">Review follow-ups</a>
                </div>
            @endif

            @if ($byCategory->isNotEmpty())
                <div class="rounded-xl border bg-white p-4">
                    <h3 class="font-semibold">By category</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($byCategory->take(6) as $name => $count)
                            <li class="flex justify-between"><span>{{ $name }}</span><span class="font-semibold">{{ $count }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
