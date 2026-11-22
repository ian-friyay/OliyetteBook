<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, ['all', 'pending', 'approved', 'removed'], true)) {
            $filter = 'all';
        }

        $query = DB::table('reviews as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->join('cottages as c', 'c.id', '=', 'r.cottage_id')
            ->select('r.*', 'u.first_name', 'u.last_name', 'c.name as cottage_name');

        if ($filter !== 'all') {
            $query->where('r.status', $filter);
        }

        return view('admin.reviews', [
            'reviews' => $query->orderByDesc('r.created_at')->orderByDesc('r.id')->get(),
            'feedback' => DB::table('feedback as f')
                ->join('users as u', 'u.id', '=', 'f.user_id')
                ->select('f.comment', 'f.created_at', 'u.first_name', 'u.last_name')
                ->orderByDesc('f.created_at')
                ->orderByDesc('f.id')
                ->limit(100)
                ->get(),
            'filter' => $filter,
            'adminPage' => 'reviews',
            'pageTitle' => 'Reviews',
        ]);
    }

    public function action(Request $request)
    {
        $reviewId = (int) $request->input('review_id', 0);
        $action = (string) $request->input('action', '');

        if ($reviewId <= 0 || ! in_array($action, ['approve', 'remove'], true)) {
            flash('error', 'Invalid review action.');

            return redirect('/admin/reviews');
        }

        try {
            $status = DB::transaction(function () use ($reviewId, $action) {
                $review = DB::table('reviews')->where('id', $reviewId)->lockForUpdate()->first();

                if (! $review) {
                    throw new \RuntimeException('Review not found.');
                }

                if ($action === 'approve' && $review->status !== 'pending') {
                    throw new \RuntimeException('Only pending reviews can be approved.');
                }
                if ($action === 'remove' && ! in_array($review->status, ['pending', 'approved'], true)) {
                    throw new \RuntimeException('That review is already removed.');
                }

                $status = $action === 'approve' ? 'approved' : 'removed';
                DB::table('reviews')->where('id', $reviewId)->update(['status' => $status]);

                return $status;
            });

            flash('success', $status === 'approved' ? 'Review approved.' : 'Review removed from public view.');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            flash('error', 'We could not update the review. Please try again.');
        }

        return redirect('/admin/reviews');
    }
}
