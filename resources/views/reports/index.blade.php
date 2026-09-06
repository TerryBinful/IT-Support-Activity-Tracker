@extends('layouts.app')

@section('content')
    @php
        $from = $from ?? now()->startOfMonth()->toDateString();
        $to = $to ?? now()->endOfMonth()->toDateString();
        $savedPreferences = $savedPreferences ?? collect();
        $summary = $summary ?? ['total' => 0, 'completed' => 0, 'in_progress' => 0, 'pending' => 0, 'total_minutes' => 0, 'follow_ups_open' => 0];
        $preview = $preview ?? collect();
    @endphp

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold">Reports</h1>
            <p class="mt-1 text-sm text-slate-500">Preview, configure columns, and export your activity report.</p>
        </div>
        <a href="{{ route('reports.index', ['period' => 'previous_month']) }}" class="btn-secondary">Previous month</a>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-5">
        @foreach ([['Activities', $summary['total']], ['Completed', $summary['completed']], ['In progress', $summary['in_progress']], ['Time (min)', $summary['total_minutes']], ['Open follow-ups', $summary['follow_ups_open']]] as [$label, $value])
            <div class="rounded-xl border bg-white p-4"><div class="text-sm text-slate-500">{{ $label }}</div><div class="mt-1 text-2xl font-bold">{{ $value }}</div></div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border bg-white p-6">
                <h2 class="font-semibold">Date range</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="from" class="label">From</label>
                        <input id="from" type="date" value="{{ $from }}" class="input">
                    </div>
                    <div>
                        <label for="to" class="label">To</label>
                        <input id="to" type="date" value="{{ $to }}" class="input">
                    </div>
                </div>
            </section>

            <section class="rounded-xl border bg-white p-6">
                <h2 class="font-semibold">Columns</h2>
                <p class="mt-1 text-sm text-slate-500">Select the fields to include in your report.</p>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($columns as $key => $label)
                        <label class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm">
                            <input type="checkbox" class="column-checkbox rounded border-slate-300" value="{{ $key }}" data-label="{{ $label }}" @checked(in_array($key, $selected, true))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border bg-white p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold">Column order</h2>
                        <p class="mt-1 text-sm text-slate-500">Use the arrows to reorder selected columns.</p>
                    </div>
                </div>
                <ul id="column-order" class="mt-4 space-y-2">
                    @foreach ($order as $key)
                        <li class="flex items-center justify-between rounded-lg border px-3 py-2 text-sm" data-key="{{ $key }}">
                            <span>{{ $columns[$key] ?? $key }}</span>
                            <span class="flex gap-1">
                                <button type="button" class="move-up rounded border px-2 py-1 hover:bg-slate-50" aria-label="Move up">↑</button>
                                <button type="button" class="move-down rounded border px-2 py-1 hover:bg-slate-50" aria-label="Move down">↓</button>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="rounded-xl border bg-white p-6">
                <h2 class="font-semibold">Preview</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead><tr class="border-b">@foreach($order as $key)<th class="px-2 py-2 text-left">{{ $columns[$key] ?? $key }}</th>@endforeach</tr></thead>
                        <tbody>@forelse($preview as $row)<tr class="border-b">@foreach($order as $key)<td class="px-2 py-2">{{ match($key){'activity_date'=>$row->activity_date?->format('Y-m-d'),'category'=>$row->category?->name,'follow_up_required'=>$row->follow_up_required?'Yes':'No',default=>$row->{$key}??'—'} }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($order) }}" class="py-4 text-slate-500">No activities in this period.</td></tr>@endforelse</tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-xl border bg-white p-6">
                <h2 class="font-semibold">Export</h2>
                <p class="mt-1 text-sm text-slate-500">Download the current layout for the selected date range.</p>
                <div class="mt-4 flex flex-col gap-2">
                    <a href="#" data-format="xlsx" class="export-link btn">Export Excel</a>
                    <a href="#" data-format="csv" class="export-link btn-secondary">Export CSV</a>
                    <a href="#" data-format="pdf" class="export-link btn-secondary">Export PDF</a>
                </div>
            </section>

            <section class="rounded-xl border bg-white p-6">
                <h2 class="font-semibold">Save layout</h2>
                <form method="POST" action="{{ route('reports.preferences') }}" class="mt-4 space-y-4" id="save-preference-form">
                    @csrf
                    <div>
                        <label for="name" class="label">Layout name</label>
                        <input id="name" name="name" type="text" required class="input" placeholder="Monthly summary">
                    </div>
                    <div id="preference-fields"></div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="is_default" value="1" class="rounded border-slate-300">
                        Set as default layout
                    </label>
                    <button type="submit" class="btn w-full">Save layout</button>
                </form>
            </section>

            @if ($savedPreferences->isNotEmpty())
                <section class="rounded-xl border bg-white p-6">
                    <h2 class="font-semibold">Saved layouts</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach ($savedPreferences as $preference)
                            <li class="rounded-lg border px-3 py-2">
                                <div class="font-medium">{{ $preference->name }}</div>
                                <div class="text-slate-500">
                                    {{ count($preference->columns) }} columns
                                    @if ($preference->is_default)
                                        · Default
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <a href="{{ route('reports.index', ['preset' => $preference->id]) }}" class="font-semibold underline">Load layout</a>
                                    <form method="POST" action="{{ route('reports.preferences.duplicate', $preference) }}">@csrf<button class="font-semibold underline" type="submit">Duplicate</button></form>
                                    @if (! $preference->is_default)
                                        <form method="POST" action="{{ route('reports.preferences.default', $preference) }}">@csrf<button class="font-semibold underline" type="submit">Set default</button></form>
                                    @endif
                                    <form method="POST" action="{{ route('reports.preferences.destroy', $preference) }}" onsubmit="return confirm('Delete this report preset?')">@csrf @method('DELETE')<button class="font-semibold text-red-700 underline" type="submit">Delete</button></form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>

    <script>
        (() => {
            const orderList = document.getElementById('column-order');
            const checkboxes = Array.from(document.querySelectorAll('.column-checkbox'));
            const fromInput = document.getElementById('from');
            const toInput = document.getElementById('to');
            const preferenceFields = document.getElementById('preference-fields');
            const exportLinks = document.querySelectorAll('.export-link');
            const saveForm = document.getElementById('save-preference-form');

            const selectedKeys = () => checkboxes.filter((box) => box.checked).map((box) => box.value);

            const orderedKeys = () => Array.from(orderList.querySelectorAll('[data-key]')).map((item) => item.dataset.key);

            const syncOrderList = () => {
                const selected = selectedKeys();
                const current = orderedKeys().filter((key) => selected.includes(key));
                selected.forEach((key) => {
                    if (!current.includes(key)) current.push(key);
                });

                orderList.innerHTML = '';

                current.forEach((key) => {
                    const checkbox = checkboxes.find((box) => box.value === key);
                    if (!checkbox) return;

                    const item = document.createElement('li');
                    item.className = 'flex items-center justify-between rounded-lg border px-3 py-2 text-sm';
                    item.dataset.key = key;
                    item.innerHTML = `
                        <span>${checkbox.dataset.label}</span>
                        <span class="flex gap-1">
                            <button type="button" class="move-up rounded border px-2 py-1 hover:bg-slate-50" aria-label="Move up">↑</button>
                            <button type="button" class="move-down rounded border px-2 py-1 hover:bg-slate-50" aria-label="Move down">↓</button>
                        </span>
                    `;
                    orderList.appendChild(item);
                });
            };

            const moveItem = (key, direction) => {
                const items = Array.from(orderList.children);
                const index = items.findIndex((item) => item.dataset.key === key);
                const target = index + direction;
                if (target < 0 || target >= items.length) return;
                if (direction < 0) {
                    orderList.insertBefore(items[index], items[target]);
                } else {
                    orderList.insertBefore(items[target], items[index]);
                }
            };

            const buildQuery = () => {
                const params = new URLSearchParams({
                    from: fromInput.value,
                    to: toInput.value,
                    columns: orderedKeys().join(','),
                    order: orderedKeys().join(','),
                });
                return params.toString();
            };

            const syncHiddenFields = () => {
                preferenceFields.innerHTML = '';
                orderedKeys().forEach((key) => {
                    ['columns', 'order'].forEach((name) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `${name}[]`;
                        input.value = key;
                        preferenceFields.appendChild(input);
                    });
                });
            };

            const refresh = () => {
                syncOrderList();
                syncHiddenFields();
                exportLinks.forEach((link) => {
                    link.href = `{{ url('/reports/export') }}/${link.dataset.format}?${buildQuery()}`;
                });
            };

            checkboxes.forEach((box) => box.addEventListener('change', refresh));
            fromInput.addEventListener('change', refresh);
            toInput.addEventListener('change', refresh);

            orderList.addEventListener('click', (event) => {
                const item = event.target.closest('[data-key]');
                if (!item) return;
                if (event.target.classList.contains('move-up')) moveItem(item.dataset.key, -1);
                if (event.target.classList.contains('move-down')) moveItem(item.dataset.key, 1);
                refresh();
            });

            saveForm.addEventListener('submit', syncHiddenFields);
            refresh();
        })();
    </script>
@endsection
