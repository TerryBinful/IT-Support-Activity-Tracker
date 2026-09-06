@php $recurring = $recurring ?? null; @endphp
<div class="grid gap-4 md:grid-cols-2">
<div class="md:col-span-2"><label class="label">Title</label><input name="title" class="input" value="{{ old('title', $recurring?->title) }}" required></div>
<div class="md:col-span-2"><label class="label">Description</label><textarea name="description" class="input" rows="3">{{ old('description', $recurring?->description) }}</textarea></div>
<div><label class="label">Category</label><select name="category_id" class="input"><option value="">Uncategorised</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $recurring?->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
<div><label class="label">Priority</label><select name="priority" class="input">@foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}" @selected(old('priority', $recurring?->priority ?? 'medium') === $p)>{{ str($p)->title() }}</option>@endforeach</select></div>
<div><label class="label">Recurrence</label><select name="recurrence_type" class="input">@foreach(['daily','weekly','monthly'] as $type)<option value="{{ $type }}" @selected(old('recurrence_type', $recurring?->recurrence_type) === $type)>{{ str($type)->title() }}</option>@endforeach</select></div>
<div><label class="label">Day (weekly 0-6, monthly 1-31)</label><input name="recurrence_day" type="number" min="0" max="31" class="input" value="{{ old('recurrence_day', $recurring?->recurrence_day) }}"></div>
</div>
