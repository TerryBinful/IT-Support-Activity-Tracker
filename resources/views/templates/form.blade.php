@php $template = $template ?? null; @endphp
<div class="grid gap-4 md:grid-cols-2">
<div class="md:col-span-2"><label class="label">Title</label><input name="title" class="input" value="{{ old('title', $template?->title) }}" required></div>
<div class="md:col-span-2"><label class="label">Description</label><textarea name="description" class="input" rows="3">{{ old('description', $template?->description) }}</textarea></div>
<div><label class="label">Category</label><select name="category_id" class="input"><option value="">Uncategorised</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $template?->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
<div><label class="label">Default priority</label><select name="default_priority" class="input">@foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}" @selected(old('default_priority', $template?->default_priority ?? 'medium') === $p)>{{ str($p)->title() }}</option>@endforeach</select></div>
<div><label class="label">Default status</label><select name="default_status" class="input">@foreach(['completed','in_progress','pending','on_hold','cancelled'] as $s)<option value="{{ $s }}" @selected(old('default_status', $template?->default_status ?? 'completed') === $s)>{{ str($s)->replace('_',' ')->title() }}</option>@endforeach</select></div>
<label class="flex items-center gap-2"><input type="checkbox" name="default_follow_up_required" value="1" @checked(old('default_follow_up_required', $template?->default_follow_up_required ?? false))> Default follow-up required</label>
<div class="md:col-span-2"><label class="label">Default follow-up action</label><textarea name="default_follow_up_action" class="input" rows="2">{{ old('default_follow_up_action', $template?->default_follow_up_action) }}</textarea></div>
<label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template?->is_active ?? true))> Active</label>
</div>
