<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $cottages = active_cottages();

        return view('home', [
            'cottages' => $cottages,
            'pageTitle' => 'Home',
        ]);
    }
}
