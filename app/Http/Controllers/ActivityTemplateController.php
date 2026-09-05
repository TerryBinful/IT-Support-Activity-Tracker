<?php

namespace App\Http\Controllers;

use App\Models\ActivityTemplate;
use App\Models\Category;
use Illuminate\Http\Request;

class ActivityTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = $request->user()->activityTemplates()->with('category')->orderBy('title')->paginate(20);

        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('templates.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $request->user()->activityTemplates()->create($data);

        return redirect()->route('templates.index')->with('success', 'Template saved.');
    }

    public function edit(Request $request, ActivityTemplate $template)
    {
        $this->authorize('update', $template);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('templates.edit', compact('template', 'categories'));
    }

    public function update(Request $request, ActivityTemplate $template)
    {
        $this->authorize('update', $template);
        $template->update($this->validated($request));

        return redirect()->route('templates.index')->with('success', 'Template updated.');
    }

    public function destroy(Request $request, ActivityTemplate $template)
    {
        $this->authorize('delete', $template);
        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Template deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'default_priority' => 'required|in:low,medium,high,critical',
            'default_status' => 'required|in:completed,in_progress,pending,on_hold,cancelled',
            'default_follow_up_required' => 'nullable|boolean',
            'default_follow_up_action' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]) + [
            'default_follow_up_required' => $request->boolean('default_follow_up_required'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
