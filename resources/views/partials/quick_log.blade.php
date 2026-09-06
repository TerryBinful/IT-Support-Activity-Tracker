<section class="rounded-xl border bg-white p-5">
    <h2 class="text-lg font-semibold">Quick Log</h2>
    <p class="mt-1 text-sm text-slate-500">Record something you did in seconds.</p>

    <form method="POST" action="{{ route('activities.quick') }}" class="mt-4 space-y-4">
        @csrf
        <input type="hidden" name="quick_log_key" value="{{ old('quick_log_key', (string) \Illuminate\Support\Str::uuid()) }}">
        <div>
            <label for="quick_title" class="label">What did you do?</label>
            <input id="quick_title" name="title" type="text" required autofocus class="input" placeholder="Resolved Outlook issue for Finance user">
        </div>
        <div>
            <label for="quick_category" class="label">Category</label>
            <select id="quick_category" name="category_id" class="input">
                <option value="">Uncategorised</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <details class="text-sm">
            <summary class="cursor-pointer font-medium text-slate-700">Add more details</summary>
            <div class="mt-3">
                <label for="quick_description" class="label">Description</label>
                <textarea id="quick_description" name="description" rows="2" class="input"></textarea>
            </div>
        </details>
        <button type="submit" class="btn">Save Activity</button>
    </form>
</section>
