@extends('layouts.app')
@section('content')
<h1 class="text-3xl font-bold mb-6">Edit Recurring Activity</h1>
<form method="POST" action="{{ route('recurring.update', $recurring) }}" class="rounded-xl border bg-white p-6 space-y-4">@csrf @method('PUT') @include('recurring.form')<button class="btn">Save</button></form>
@endsection
