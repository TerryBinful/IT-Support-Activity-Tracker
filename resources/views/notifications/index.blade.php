@extends('layouts.app')
@section('content')
<div class="flex items-end justify-between mb-6"><h1 class="text-3xl font-bold">Notifications</h1>@if(auth()->user()->unreadNotifications->count())<form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn-secondary">Mark all read</button></form>@endif</div>
<div class="divide-y rounded-xl border bg-white">@forelse($notifications as $notification)<div @class(['p-5', 'bg-slate-50' => is_null($notification->read_at)])><div class="font-medium">{{ $notification->data['title'] ?? 'Notification' }}</div><p class="mt-1 text-sm text-slate-600">{{ $notification->data['message'] ?? '' }}</p><form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="mt-3">@csrf<button class="text-sm font-semibold underline">Open</button></form></div>@empty<div class="p-8 text-center text-slate-500">No notifications.</div>@endforelse</div>
@if($notifications->hasPages())<div class="mt-6">{{ $notifications->links() }}</div>@endif
@endsection
