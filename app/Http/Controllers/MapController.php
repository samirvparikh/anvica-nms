<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * Display the network map view.
     */
    public function index()
    {
        $sites = Site::all();
        return view('maps.index', compact('sites'));
    }
}
