<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardStationController extends Controller
{
    public function index(Request $request): View|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        return response()->view('404', [], 404);
    }
}
