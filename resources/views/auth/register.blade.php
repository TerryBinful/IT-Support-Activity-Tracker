@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold">Create account</h1>
            <p class="mt-1 text-sm text-slate-500">Start tracking your IT support activities.</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="rounded-xl border bg-white p-6">
            @csrf

            <div class="mb-4">
                <label for="name" class="label">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="input">
            </div>

            <div class="mb-4">
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input">
            </div>

            <div class="mb-4">
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" required class="input">
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="input">
            </div>

            <button type="submit" class="btn w-full">Create account</button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-500">
            Already registered?
            <a href="{{ route('login') }}" class="font-semibold text-slate-900 underline">Sign in</a>
        </p>
    </div>
@endsection
