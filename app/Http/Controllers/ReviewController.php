<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Review;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = (int) $user->id;
        $role = $user->role;

        if ($role === 'owner') {
            $feedback = DB::table('feedback as f')
                ->join('users as u', 'u.id', '=', 'f.user_id')
                ->select('f.comment', 'f.created_at', 'u.first_name', 'u.last_name')
                ->orderByDesc('f.created_at')
                ->orderByDesc('f.id')
                ->limit(100)
                ->get();

            return view('reviews', [
                'eligible' => collect(),
                'selectedBooking' => null,
                'feedback' => $feedback,
                'role' => $role,
                'pageTitle' => 'User Reviews',
            ]);
        }

        $eligible = DB::table('bookings as b')
            ->join('cottages as c', 'c.id', '=', 'b.cottage_id')
            ->leftJoin('reviews as r', 'r.booking_id', '=', 'b.id')
            ->where('b.user_id', $userId)
            ->where('b.status', 'completed')
            ->whereNull('r.id')
            ->select('b.id', 'b.receipt_no', 'b.check_in', 'b.check_out', 'c.name as cottage_name', 'c.location as cottage_location')
            ->orderByDesc('b.check_out')
            ->orderByDesc('b.id')
            ->get();

        $selectedBooking = null;
        $selectedId = (int) $request->query('booking', 0);
        if ($selectedId > 0) {
            $selectedBooking = $eligible->first(fn ($booking) => (int) $booking->id === $selectedId);
        }
        if (! $selectedBooking && count($eligible) === 1) {
            $selectedBooking = $eligible->first();
        }

        return view('reviews', [
            'eligible' => $eligible,
            'selectedBooking' => $selectedBooking,
            'feedback' => collect(),
            'role' => $role,
            'pageTitle' => 'User Reviews',
        ]);
    }

    public function storeFeedback(Request $request)
    {
        $comment = trim((string) $request->input('comment', ''));

        if ($comment === '') {
            flash('error', 'Please write your comment before submitting.');

            return redirect()->route('reviews');
        }
        if (mb_strlen($comment) > 2000) {
            flash('error', 'Comments must be 2,000 characters or fewer.');

            return redirect()->route('reviews');
        }

        Feedback::create([
            'user_id' => auth()->user()->id,
            'comment' => $comment,
        ]);

        flash('success', 'Thank you! Your comment about the resort was submitted.');

        return redirect()->route('reviews');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $bookingId = (int) $request->input('booking_id', 0);
        $rating = (int) $request->input('rating', 0);
        $comment = trim((string) $request->input('comment', ''));

        if ($bookingId <= 0 || $rating < 1 || $rating > 5) {
            flash('error', 'Choose a completed stay and a rating from 1 to 5 stars.');

            return redirect()->route('reviews');
        }
        if (mb_strlen($comment) > 2000) {
            flash('error', 'Reviews must be 2,000 characters or fewer.');

            return redirect('/reviews?booking=' . $bookingId);
        }

        $booking = DB::table('bookings as b')
            ->join('cottages as c', 'c.id', '=', 'b.cottage_id')
            ->leftJoin('reviews as r', 'r.booking_id', '=', 'b.id')
            ->where('b.id', $bookingId)
            ->where('b.user_id', $user->id)
            ->where('b.status', 'completed')
            ->whereNull('r.id')
            ->select('b.*', 'c.name as cottage_name')
            ->first();

        if (! $booking) {
            flash('error', 'That stay is not eligible for a review.');

            return redirect()->route('reviews');
        }

        try {
            Review::create([
                'booking_id' => $bookingId,
                'user_id' => $user->id,
                'cottage_id' => $booking->cottage_id,
                'rating' => $rating,
                'comment' => $comment !== '' ? $comment : null,
                'status' => 'approved',
            ]);

            flash('success', 'Thank you! Your review of ' . $booking->cottage_name . ' was submitted.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                flash('error', 'This stay has already been reviewed.');
            } else {
                report($e);
                flash('error', 'We could not save your review. Please try again.');
            }
        }

        return redirect()->route('reviews');
    }
}
