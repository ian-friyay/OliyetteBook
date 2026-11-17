<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $roleFilter = (string) $request->query('role', 'all');
        $statusFilter = (string) $request->query('status', 'all');
        $q = trim((string) $request->query('q', ''));

        if (! in_array($roleFilter, ['all', 'guest', 'owner'], true)) {
            $roleFilter = 'all';
        }
        if (! in_array($statusFilter, ['all', 'active', 'pending', 'deactivated'], true)) {
            $statusFilter = 'all';
        }

        if ($request->isMethod('post')) {
            $userId = (int) $request->input('user_id', 0);
            $action = (string) $request->input('action', '');
            $return = safe_next($request->input('return', ''), '/admin/users');

            $targets = ['activate' => 'active', 'deactivate' => 'deactivated'];
            if ($userId <= 0 || ! isset($targets[$action])) {
                flash('error', 'Invalid user action.');

                return redirect($return);
            }

            $account = DB::table('users')
                ->where('id', $userId)
                ->whereIn('role', ['guest', 'owner'])
                ->first();

            if (! $account) {
                flash('error', 'Account not found.');

                return redirect($return);
            }

            $status = $targets[$action];
            DB::table('users')->where('id', $userId)->update(['status' => $status]);

            flash('success', $account->first_name . ' ' . $account->last_name . ' is now ' . status_label($status) . '.');

            return redirect($return);
        }

        $query = DB::table('users as u')
            ->whereIn('u.role', ['guest', 'owner'])
            ->select('u.*')
            ->selectRaw('(SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS booking_count');

        if ($roleFilter !== 'all') {
            $query->where('u.role', $roleFilter);
        }
        if ($statusFilter !== 'all') {
            $query->where('u.status', $statusFilter);
        }
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('u.first_name', 'like', $like)
                    ->orWhere('u.last_name', 'like', $like)
                    ->orWhere('u.email', 'like', $like)
                    ->orWhere('u.phone', 'like', $like);
            });
        }

        $users = $query->orderByDesc('u.created_at')->orderByDesc('u.id')->limit(300)->get();

        $returnParams = [];
        if ($roleFilter !== 'all') {
            $returnParams['role'] = $roleFilter;
        }
        if ($statusFilter !== 'all') {
            $returnParams['status'] = $statusFilter;
        }
        if ($q !== '') {
            $returnParams['q'] = $q;
        }
        $returnUrl = '/admin/users' . ($returnParams ? '?' . http_build_query($returnParams) : '');

        return view('admin.users', [
            'users' => $users,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'q' => $q,
            'returnUrl' => $returnUrl,
            'adminPage' => 'users',
            'pageTitle' => 'Users',
        ]);
    }
}
