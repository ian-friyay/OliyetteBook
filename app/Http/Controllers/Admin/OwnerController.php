<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
        $filter = (string) $request->query('status', 'all');
        $allowedFilters = ['all', 'pending', 'active', 'deactivated'];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $query = DB::table('users as u')
            ->where('u.role', 'owner')
            ->select('u.*')
            ->selectRaw('(SELECT COUNT(*) FROM cottages c WHERE c.owner_id = u.id) AS cottage_count')
            ->selectRaw("(SELECT COUNT(*) FROM cottages c WHERE c.owner_id = u.id AND c.status = 'active') AS active_count");

        if ($filter !== 'all') {
            $query->where('u.status', $filter);
        }

        $owners = $query->orderByRaw("CASE u.status WHEN 'pending' THEN 0 WHEN 'active' THEN 1 ELSE 2 END")
            ->orderByDesc('u.created_at')
            ->orderByDesc('u.id')
            ->get();

        return view('admin.owners', [
            'owners' => $owners,
            'filter' => $filter,
            'adminPage' => 'owners',
            'pageTitle' => 'Cottage Owners',
        ]);
    }

    public function action(Request $request)
    {
        $ownerId = (int) $request->input('owner_id', 0);
        $action = (string) $request->input('action', '');
        $return = safe_next($request->input('return', ''), '/admin/owners');

        $targets = [
            'approve' => 'active',
            'decline' => 'deactivated',
            'reactivate' => 'active',
            'deactivate' => 'deactivated',
        ];

        if ($ownerId <= 0 || ! isset($targets[$action])) {
            flash('error', 'Invalid owner action.');

            return redirect($return);
        }

        $owner = DB::table('users')->where('id', $ownerId)->where('role', 'owner')->first();

        if (! $owner) {
            flash('error', 'Owner account not found.');

            return redirect($return);
        }

        $status = $targets[$action];

        DB::table('users')->where('id', $ownerId)->where('role', 'owner')->update(['status' => $status]);

        $message = $status === 'active'
            ? 'Owner account for ' . $owner->first_name . ' ' . $owner->last_name . ' is now active.'
            : 'Owner account for ' . $owner->first_name . ' ' . $owner->last_name . ' was deactivated.';

        flash('success', $message);

        return redirect($return);
    }
}
