<?php

namespace App\Http\Controllers;

use App\Models\EventUserView;
use Illuminate\Http\Request;

class EventFilterController extends Controller
{

    public function index(Request $request)
    {
        $query = EventUserView::query();

        if ($request->filled('title')) {
            $query->filterByTitle($request->input('title'));
        }

        if ($request->filled('user_name')) {
            $query->filterByUserName($request->input('user_name'));
        }

        $events = $query->paginate(15)->withQueryString();

        return view('events.index', compact('events'));
    }
}