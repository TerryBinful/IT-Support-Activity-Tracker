@extends('layouts.app')
@section('content')
<h1 class="text-3xl font-bold mb-6">New Template</h1>
<form method="POST" action="{{ route('templates.store') }}" class="rounded-xl border bg-white p-6 space-y-4">
@csrf
@include('templates.form')
<div class="flex gap-2"><button class="btn">Save</button><a href="{{ route('templates.index') }}" class="btn-secondary">Cancel</a></div>
</form>
@endsection
