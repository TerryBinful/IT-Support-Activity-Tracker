<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'IT Activity Tracker') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    @auth
        <nav class="border-b bg-white">
            <div class="mx-auto max-w-6xl px-4 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-6">
                        <a href="{{ route('dashboard') }}" class="text-lg font-bold">{{ config('app.name', 'IT Activity Tracker') }}</a>
                        <div class="hidden flex-wrap items-center gap-4 text-sm font-semibold md:flex">
                            <a href="{{ route('dashboard') }}" @class(['hover:text-slate-600', 'text-slate-900' => request()->routeIs('dashboard'), 'text-slate-500' => !request()->routeIs('dashboard')])>Dashboard</a>
                            <a href="{{ route('activities.index') }}" @class(['hover:text-slate-600', 'text-slate-900' => request()->routeIs('activities.*'), 'text-slate-500' => !request()->routeIs('activities.*')])>Activities</a>
                            <a href="{{ route('follow-ups.index') }}" @class(['hover:text-slate-600', 'text-slate-900' => request()->routeIs('follow-ups.*'), 'text-slate-500' => !request()->routeIs('follow-ups.*')])>Follow-ups</a>
                            <a href="{{ route('calendar.index') }}" @class(['hover:text-slate-600', 'text-slate-900' => request()->routeIs('calendar.*'), 'text-slate-500' => !request()->routeIs('calendar.*')])>Calendar</a>
                            <a href="{{ route('reports.index') }}" @class(['hover:text-slate-600', 'text-slate-900' => request()->routeIs('reports.*'), 'text-slate-500' => !request()->routeIs('reports.*')])>Reports</a>
                            <a href="{{ route('templates.index') }}" @class(['hover:text-slate-600', 'text-slate-900' => request()->routeIs('templates.*'), 'text-slate-500' => !request()->routeIs('templates.*')])>Templates</a>
                            <a href="{{ route('recurring.index') }}" @class(['hover:text-slate-600', 'text-slate-900' => request()->routeIs('recurring.*'), 'text-slate-500' => !request()->routeIs('recurring.*')])>Recurring</a>
                            <a href="{{ route('notifications.index') }}" @class(['hover:text-slate-600', 'text-slate-900' => request()->routeIs('notifications.*'), 'text-slate-500' => !request()->routeIs('notifications.*')])>
                                Notifications
                                @if (($unread = auth()->user()->unreadNotifications()->count()) > 0)
                                    <span class="ml-1 rounded-full bg-slate-900 px-2 py-0.5 text-xs text-white">{{ $unread }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('search.index') }}" class="flex gap-2">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search activities..." class="input max-w-xs">
                        <button type="submit" class="btn-secondary">Search</button>
                    </form>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-slate-400">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-slate-500 hover:text-slate-800">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
