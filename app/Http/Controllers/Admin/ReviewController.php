<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $filter = (string) ($request->query('status') ?? 'pending');
        $allowedFilters = ['all', 'pending', 'approved', 'removed'];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'pending';
        }

        if ($request->isMethod('post')) {
            $reviewId = (int) $request->input('review_id', 0);
            $action = (string) $request->input('action', '');
            $return = safe_next($request->input('return', ''), '/admin/reviews');

            $targets = [
                'approve' => 'approved',
                'remove' => 'removed',
                'pending' => 'pending',
            ];

            if ($reviewId <= 0 || ! isset($targets[$action])) {
                flash('error', 'Invalid review action.');

                return redirect($return);
            }

            $review = DB::table('reviews')->where('id', $reviewId)->first();

            if (! $review) {
                flash('error', 'Review not found.');

                return redirect($return);
            }

            $status = $targets[$action];
            DB::table('reviews')->where('id', $reviewId)->update(['status' => $status]);

            flash('success', 'Review marked ' . status_label($status) . '.');

            return redirect($return);
        }

        $query = DB::table('reviews as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->join('cottages as c', 'c.id', '=', 'r.cottage_id')
            ->select('r.*', 'u.first_name', 'u.last_name', 'u.email', 'c.name as cottage_name');

        if ($filter !== 'all') {
            $query->where('r.status', $filter);
        }

        $reviews = $query->orderByDesc('r.created_at')->orderByDesc('r.id')->limit(300)->get();

        $returnUrl = '/admin/reviews' . ($filter !== 'all' ? '?status=' . urlencode($filter) : '');

        return view('admin.reviews', [
            'reviews' => $reviews,
            'filter' => $filter,
            'returnUrl' => $returnUrl,
            'adminPage' => 'reviews',
            'pageTitle' => 'Review Moderation',
        ]);
    }
}
