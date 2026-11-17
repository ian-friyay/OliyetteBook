<?php

namespace App\Http\Controllers;

class RoomController extends Controller
{
    public function index()
    {
        return redirect()->route('booking', ['type' => 'rooms']);
    }
}
