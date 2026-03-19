<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function about(): View
    {
        return view('about');
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function dashboard(): View
    {
        return view('dashboard');
    }

    public function privacyPolicy(): View
    {
        return view('privacy-policy');
    }

    public function forbidden(): View
    {
        return view('403');
    }

    public function notFound(): View
    {
        return view('404');
    }
}