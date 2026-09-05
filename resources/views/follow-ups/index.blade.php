@extends('layouts.app')
@section('content')
<h1 class="text-3xl font-bold mb-6">Follow-ups</h1>
<div class="grid gap-6 lg:grid-cols-2">
<section class="rounded-xl border bg-white p-5"><h2 class="font-semibold text-red-700">Overdue</h2><div class="mt-3 space-y-3">@forelse($overdue as $item)<div class="rounded-lg border p-3"><a href="{{ route('activities.show', $item) }}" class="font-medium hover:underline">{{ $item->title }}</a><p class="text-sm text-slate-500">Due {{ $item->follow_up_due_at?->format('d M') }}</p><form method="POST" action="{{ route('follow-ups.complete', $item) }}" class="mt-2">@csrf<button class="btn-secondary">Complete follow-up</button></form></div>@empty<p class="text-sm text-slate-500">None</p>@endforelse</div></section>
<section class="rounded-xl border bg-white p-5"><h2 class="font-semibold text-amber-700">Due today</h2><div class="mt-3 space-y-3">@forelse($dueToday as $item)<div class="rounded-lg border p-3"><a href="{{ route('activities.show', $item) }}" class="font-medium hover:underline">{{ $item->title }}</a></div>@empty<p class="text-sm text-slate-500">None</p>@endforelse</div></section>
<section class="rounded-xl border bg-white p-5"><h2 class="font-semibold text-emerald-700">Upcoming</h2><div class="mt-3 space-y-3">@forelse($upcoming as $item)<div class="rounded-lg border p-3"><a href="{{ route('activities.show', $item) }}" class="font-medium hover:underline">{{ $item->title }}</a><p class="text-sm text-slate-500">Due {{ $item->follow_up_due_at?->format('d M') }}</p></div>@empty<p class="text-sm text-slate-500">None</p>@endforelse</div></section>
<section class="rounded-xl border bg-white p-5"><h2 class="font-semibold">No due date</h2><div class="mt-3 space-y-3">@forelse($noDate as $item)<div class="rounded-lg border p-3"><a href="{{ route('activities.show', $item) }}" class="font-medium hover:underline">{{ $item->title }}</a></div>@empty<p class="text-sm text-slate-500">None</p>@endforelse</div></section>
</div>
@endsection
