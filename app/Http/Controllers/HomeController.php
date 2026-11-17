<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $cottages = active_cottages();

        return view('home', [
            'cottages' => $cottages,
            'pageTitle' => 'Home',
        ]);
    }
}
