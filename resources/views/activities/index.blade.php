@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold">Activities</h1>
            <p class="mt-1 text-sm text-slate-500">Search, filter, and manage your work log.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('activities.index', ['view' => 'today']) }}" class="btn-secondary">Today</a>
            <a href="{{ route('activities.index', ['view' => 'active']) }}" class="btn-secondary">Active</a>
            <a href="{{ route('activities.index', ['view' => 'follow-ups']) }}" class="btn-secondary">Follow-ups</a>
            <a href="{{ route('activities.create') }}" class="btn">+ New Activity</a>
        </div>
    </div>

    <div class="mt-6">
        @include('partials.quick_log', ['categories' => $categories])
    </div>

    <form method="GET" action="{{ route('activities.index') }}" class="mt-6 grid gap-4 rounded-xl border bg-white p-5 md:grid-cols-6">
        <div class="md:col-span-2">
            <label for="q" class="label">Search</label>
            <input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="Title, outcome, reference..." class="input">
        </div>
        <div>
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input">
                <option value="">All</option>
                @foreach (['completed', 'in_progress', 'pending', 'on_hold', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="category" class="label">Category</label>
            <select id="category" name="category" class="input">
                <option value="">All</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="from" class="label">From</label>
            <input id="from" name="from" type="date" value="{{ request('from') }}" class="input">
        </div>
        <div>
            <label for="to" class="label">To</label>
            <input id="to" name="to" type="date" value="{{ request('to') }}" class="input">
        </div>
        <div class="flex items-end gap-2 md:col-span-6">
            <button type="submit" class="btn">Apply filters</button>
            <a href="{{ route('activities.index') }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-xl border bg-white">
        <div class="divide-y">
            @forelse ($activities as $activity)
                <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex-1">
                        <a href="{{ route('activities.show', $activity) }}" class="font-medium hover:underline">{{ $activity->title }}</a>
                        <div class="mt-1 flex flex-wrap gap-2 text-sm text-slate-500">
                            <span>{{ $activity->activity_date?->format('d M Y') }}</span>
                            <span>{{ $activity->category?->name ?? 'Uncategorised' }}</span>
                            <span>{{ str($activity->status)->replace('_', ' ')->title() }}</span>
                            @if ($activity->duration_minutes)
                                <span>{{ $activity->formattedDuration() }}</span>
                            @endif
                            @if ($activity->follow_up_required)
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-amber-900">Follow-up</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($activity->status === 'in_progress')
                            <form method="POST" action="{{ route('activities.complete', $activity) }}">@csrf<button class="btn">Complete</button></form>
                        @elseif (! in_array($activity->status, ['completed', 'cancelled']))
                            <form method="POST" action="{{ route('activities.start', $activity) }}">@csrf<button class="btn-secondary">Start Task</button></form>
                        @endif
                        <a href="{{ route('activities.edit', $activity) }}" class="btn-secondary">Edit</a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    No activities found. Try changing your filters or create a new activity.
                </div>
            @endforelse
        </div>
    </div>

    @if ($activities->hasPages())
        <div class="mt-6">{{ $activities->links() }}</div>
    @endif
@endsection
