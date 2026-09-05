@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Add Activity</h1>
        <p class="mt-1 text-sm text-slate-500">Log the work while the details are fresh.</p>
    </div>

    @if ($templates->isNotEmpty())
        <form method="GET" action="{{ route('activities.create') }}" class="mb-6 rounded-xl border bg-white p-4">
            <label for="template" class="label">Use template</label>
            <div class="flex gap-2">
                <select id="template" name="template" class="input">
                    <option value="">Choose a template...</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" @selected(request('template') == $template->id)>{{ $template->title }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary">Load</button>
            </div>
        </form>
    @endif

    <form method="POST" action="{{ route('activities.store') }}" class="rounded-xl border bg-white p-6">
        @csrf
        @include('activities.form', ['defaults' => $defaults ?? []])
        <div class="mt-6 flex justify-end gap-3">
            <a class="btn-secondary" href="{{ route('activities.index') }}">Cancel</a>
            <button class="btn">Save activity</button>
        </div>
    </form>
@endsection
