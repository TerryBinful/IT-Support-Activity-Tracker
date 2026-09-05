@extends('layouts.app')
@section('content')
<div class="flex items-end justify-between mb-6"><h1 class="text-3xl font-bold">Calendar</h1><form method="GET"><input type="month" name="month" value="{{ $month }}" class="input" onchange="this.form.submit()"></form></div>
<div class="grid gap-6 lg:grid-cols-3">
<div class="lg:col-span-2 rounded-xl border bg-white p-5">
<h2 class="font-semibold mb-4">{{ $start->format('F Y') }}</h2>
<div class="grid grid-cols-7 gap-2 text-center text-xs font-semibold text-slate-500"><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div></div>
<div class="mt-2 grid grid-cols-7 gap-2">@php $day = $start->copy()->startOfWeek(\Carbon\Carbon::MONDAY); $endGrid = $start->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY); @endphp
@while($day <= $endGrid)
@php $key = $day->toDateString(); $count = $counts[$key] ?? 0; @endphp
<a href="{{ route('calendar.index', ['month' => $month, 'date' => $key]) }}" @class(['block rounded-lg border p-2 text-center text-sm', 'bg-slate-900 text-white' => $selectedDate === $key, 'opacity-40' => !$day->isSameMonth($start), 'border-slate-300' => $day->isSameMonth($start)])><div>{{ $day->day }}</div>@if($count)<div class="text-xs">{{ $count }}</div>@endif</a>
@php $day->addDay(); @endphp
@endwhile
</div>
</div>
<div class="rounded-xl border bg-white p-5"><div class="flex items-center justify-between"><h2 class="font-semibold">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</h2><a href="{{ route('activities.create') }}" class="text-sm font-semibold underline">Quick add</a></div><div class="mt-4 space-y-3">@forelse($dayActivities as $activity)<a href="{{ route('activities.show', $activity) }}" class="block rounded-lg border p-3 hover:bg-slate-50"><div class="font-medium">{{ $activity->title }}</div><div class="text-sm text-slate-500">{{ $activity->category?->name }}</div></a>@empty<p class="text-sm text-slate-500">No activities on this day.</p>@endforelse</div></div>
</div>
@endsection
