@extends('layouts.app')
@section('content')
<div class="flex items-end justify-between mb-6"><div><h1 class="text-3xl font-bold">Templates</h1><p class="mt-1 text-sm text-slate-500">Reusable activity patterns.</p></div><a href="{{ route('templates.create') }}" class="btn">+ New Template</a></div>
<div class="divide-y rounded-xl border bg-white">@forelse($templates as $template)<div class="flex items-center justify-between p-5"><div><div class="font-medium">{{ $template->title }}</div><div class="text-sm text-slate-500">{{ $template->category?->name ?? 'Uncategorised' }}</div></div><div class="flex gap-2"><a href="{{ route('activities.create', ['template' => $template->id]) }}" class="btn-secondary">Use</a><a href="{{ route('templates.edit', $template) }}" class="btn-secondary">Edit</a><form method="POST" action="{{ route('templates.destroy', $template) }}" onsubmit="return confirm('Delete template?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form></div></div>@empty<div class="p-8 text-center text-slate-500">No templates yet.</div>@endforelse</div>
@if($templates->hasPages())<div class="mt-6">{{ $templates->links() }}</div>@endif
@endsection
