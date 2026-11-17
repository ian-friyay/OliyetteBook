<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $adminCounts = [
            'owners' => (int) DB::table('users')->where('role', 'owner')->where('status', 'pending')->count(),
            'cottages' => (int) DB::table('cottages')->where('status', 'pending')->count(),
            'bookings' => (int) DB::table('bookings')->where('status', 'pending')->count(),
            'reviews' => (int) DB::table('reviews')->where('status', 'pending')->count(),
        ];

        $pendingOwners = DB::table('users as u')
            ->where('u.role', 'owner')
            ->where('u.status', 'pending')
            ->select('u.*')
            ->selectRaw('(SELECT COUNT(*) FROM cottages c WHERE c.owner_id = u.id) AS cottage_count')
            ->selectRaw('(SELECT c.name FROM cottages c WHERE c.owner_id = u.id ORDER BY c.id LIMIT 1) AS first_cottage')
            ->orderByDesc('u.created_at')
            ->orderByDesc('u.id')
            ->get();

        $pendingBookings = DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('cottages as c', 'c.id', '=', 'b.cottage_id')
            ->leftJoin('rooms as rm', 'rm.id', '=', 'b.room_id')
            ->where('b.status', 'pending')
            ->select('b.*', 'u.first_name', 'u.last_name', 'u.email')
            ->selectRaw('COALESCE(c.name, rm.name) AS cottage_name')
            ->orderByDesc('b.created_at')
            ->orderByDesc('b.id')
            ->limit(6)
            ->get();

        $recentInquiries = DB::table('inquiries')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'adminCounts' => $adminCounts,
            'pendingOwners' => $pendingOwners,
            'pendingBookings' => $pendingBookings,
            'recentInquiries' => $recentInquiries,
            'adminPage' => 'dashboard',
            'pageTitle' => 'Admin Dashboard',
        ]);
    }
}
