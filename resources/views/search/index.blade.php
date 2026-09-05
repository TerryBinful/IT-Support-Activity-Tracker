@extends('layouts.app')
@section('content')
<h1 class="text-3xl font-bold mb-6">Search</h1>
<form method="GET" class="mb-6 flex gap-2"><input type="search" name="q" value="{{ $term }}" class="input" placeholder="Search activities..." autofocus><button class="btn">Search</button></form>
@if($term === '')<p class="text-slate-500">Enter a search term to find activities across title, description, outcome, blockers, references, categories, and tags.</p>@elseif($results instanceof \Illuminate\Support\Collection && $results->isEmpty())<p class="text-slate-500">No results for "{{ $term }}".</p>@else
<div class="divide-y rounded-xl border bg-white">@foreach($results as $activity)<a href="{{ route('activities.show', $activity) }}" class="block p-5 hover:bg-slate-50"><div class="font-medium">{{ $activity->title }}</div><div class="text-sm text-slate-500">{{ $activity->activity_date?->format('d M Y') }} · {{ $activity->category?->name ?? 'Uncategorised' }} · {{ str($activity->status)->replace('_',' ')->title() }}</div></a>@endforeach</div>
@if(method_exists($results, 'hasPages') && $results->hasPages())<div class="mt-6">{{ $results->links() }}</div>@endif
@endif
@endsection
