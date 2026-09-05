@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold">Sign in</h1>
            <p class="mt-1 text-sm text-slate-500">Log your IT activities and generate monthly reports.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="rounded-xl border bg-white p-6">
            @csrf

            <div class="mb-4">
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input">
            </div>

            <div class="mb-4">
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" required class="input">
            </div>

            <label class="mb-6 flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="rounded border-slate-300">
                Remember me
            </label>

            <button type="submit" class="btn w-full">Sign in</button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-500">
            No account?
            <a href="{{ route('register') }}" class="font-semibold text-slate-900 underline">Register</a>
        </p>
    </div>
@endsection
