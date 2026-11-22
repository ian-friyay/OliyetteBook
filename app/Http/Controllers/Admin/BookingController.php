<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $filter = (string) $request->query('status', 'all');
        $allowedFilters = ['all', 'pending', 'approved', 'completed', 'declined', 'cancelled'];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }
        $q = trim((string) $request->query('q', ''));

        $query = DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('cottages as c', 'c.id', '=', 'b.cottage_id')
            ->leftJoin('rooms as rm', 'rm.id', '=', 'b.room_id')
            ->select('b.*', 'u.first_name', 'u.last_name', 'u.email')
            ->selectRaw('COALESCE(c.name, rm.name) AS cottage_name')
            ->selectRaw('COALESCE(c.location, rm.location) AS location');

        if ($filter !== 'all') {
            $query->where('b.status', $filter);
        }
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('b.receipt_no', 'like', $like)
                    ->orWhere('u.first_name', 'like', $like)
                    ->orWhere('u.last_name', 'like', $like)
                    ->orWhere('u.email', 'like', $like)
                    ->orWhere('c.name', 'like', $like)
                    ->orWhere('rm.name', 'like', $like);
            });
        }

        $bookings = $query->orderByDesc('b.created_at')->orderByDesc('b.id')->limit(200)->get();

        $returnParams = [];
        if ($filter !== 'all') {
            $returnParams['status'] = $filter;
        }
        if ($q !== '') {
            $returnParams['q'] = $q;
        }
        $returnUrl = '/admin/bookings' . ($returnParams ? '?' . http_build_query($returnParams) : '');

        return view('admin.bookings', [
            'bookings' => $bookings,
            'filter' => $filter,
            'q' => $q,
            'returnUrl' => $returnUrl,
            'adminPage' => 'bookings',
            'pageTitle' => 'Bookings',
        ]);
    }

    public function action(Request $request)
    {
        $bookingId = (int) $request->input('booking_id', 0);
        $action = (string) $request->input('action', '');
        $return = safe_next($request->input('return', ''), '/admin/bookings');

        $targets = [
            'approve' => 'approved',
            'decline' => 'declined',
            'complete' => 'completed',
            'cancel' => 'cancelled',
        ];

        $allowedFrom = [
            'approved' => ['pending'],
            'declined' => ['pending'],
            'completed' => ['approved'],
            'cancelled' => ['pending', 'approved'],
        ];

        if ($bookingId <= 0 || ! isset($targets[$action])) {
            flash('error', 'Invalid booking action.');

            return redirect($return);
        }

        $status = $targets[$action];

        try {
            $booking = DB::transaction(function () use ($bookingId, $action, $status, $allowedFrom) {
                $booking = DB::table('bookings')->where('id', $bookingId)->lockForUpdate()->first();

                if (! $booking) {
                    throw new \RuntimeException('Booking not found.');
                }

                if (! in_array($booking->status, $allowedFrom[$status], true)) {
                    throw new \RuntimeException('That status change is not allowed from "' . status_label($booking->status) . '".');
                }

                if ($action === 'approve') {
                    if ($booking->room_id) {
                        DB::table('rooms')->where('id', $booking->room_id)->lockForUpdate()->first();
                    } else {
                        DB::table('cottages')->where('id', $booking->cottage_id)->lockForUpdate()->first();
                    }

                    $conflict = DB::table('bookings')
                        ->where('id', '!=', $bookingId)
                        ->whereIn('status', ['pending', 'approved'])
                        ->where('check_in', '<=', $booking->check_out)
                        ->where('check_out', '>=', $booking->check_in)
                        ->when($booking->room_id, function ($query) use ($booking) {
                            $query->where('room_id', $booking->room_id);
                        }, function ($query) use ($booking) {
                            $query->where('cottage_id', $booking->cottage_id);
                        })
                        ->exists();

                    if ($conflict) {
                        throw new \RuntimeException(
                            'Another active booking already covers those dates for this '
                            . ($booking->room_id ? 'room' : 'cottage')
                            . '. Decline or cancel it first.'
                        );
                    }
                }

                DB::table('bookings')->where('id', $bookingId)->update(['status' => $status]);

                return $booking;
            });

            flash('success', 'Booking #' . $booking->receipt_no . ' marked ' . status_label($status) . '.');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            flash('error', 'We could not update this booking. Please try again.');
        }

        return redirect($return);
    }
}
