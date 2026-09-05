<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->q);
        $results = collect();

        if ($term !== '') {
            $like = '%'.$term.'%';
            $results = $request->user()->activities()
                ->with('category')
                ->where(function ($q) use ($like) {
                    $q->where('title', 'ilike', $like)
                        ->orWhere('description', 'ilike', $like)
                        ->orWhere('outcome', 'ilike', $like)
                        ->orWhere('blockers', 'ilike', $like)
                        ->orWhere('reference_number', 'ilike', $like)
                        ->orWhere('follow_up_action', 'ilike', $like)
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'ilike', $like))
                        ->orWhereHas('tags', fn ($t) => $t->where('name', 'ilike', $like));
                })
                ->latest('activity_date')
                ->paginate(20)
                ->withQueryString();
        }

        return view('search.index', compact('term', 'results'));
    }
}
