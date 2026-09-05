@extends('layouts.app')
@section('content')
<div class="flex items-end justify-between mb-6"><div><h1 class="text-3xl font-bold">Recurring Activities</h1></div><a href="{{ route('recurring.create') }}" class="btn">+ New Recurring</a></div>
<div class="divide-y rounded-xl border bg-white">@forelse($recurring as $item)<div class="flex items-center justify-between p-5"><div><div class="font-medium">{{ $item->title }}</div><div class="text-sm text-slate-500">{{ str($item->recurrence_type)->title() }} · {{ $item->is_active ? 'Active' : 'Paused' }}</div></div><div class="flex gap-2"><form method="POST" action="{{ route('recurring.toggle', $item) }}">@csrf<button class="btn-secondary">{{ $item->is_active ? 'Pause' : 'Resume' }}</button></form><a href="{{ route('recurring.edit', $item) }}" class="btn-secondary">Edit</a></div></div>@empty<div class="p-8 text-center text-slate-500">No recurring activities configured.</div>@endforelse</div>
@endsection
