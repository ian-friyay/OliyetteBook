<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Cottage;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $cottages = active_cottages();
        $rooms = Room::where('status', 'active')->orderBy('id')->get();
        $type = $request->query('type', 'cottages') === 'rooms' ? 'rooms' : 'cottages';
        $selectedRoom = null;
        $selected = null;
        $unavailable = [];

        $roomId = (int) $request->query('room', 0);

        if ($roomId > 0) {
            $selectedRoom = Room::where('id', $roomId)->where('status', 'active')->first();

            if (! $selectedRoom) {
                flash('error', 'That room is not available for booking.');

                return redirect()->route('booking', ['type' => 'rooms']);
            }

            $unavailable = Booking::where('room_id', $selectedRoom->id)
                ->where('status', 'approved')
                ->where('check_out', '>=', now()->toDateString())
                ->orderBy('check_in')
                ->get(['check_in', 'check_out', 'check_in_time', 'check_out_time'])
                ->map(fn ($booking) => [
                    'check_in' => date_string($booking->check_in),
                    'check_out' => date_string($booking->check_out),
                    'check_in_time' => $booking->check_in_time ? substr((string) $booking->check_in_time, 0, 5) : '14:00',
                    'check_out_time' => $booking->check_out_time ? substr((string) $booking->check_out_time, 0, 5) : '12:00',
                ])
                ->all();
        } else {
            $selectedId = (int) $request->query('cottage', 0);
            $selected = $cottages->first(fn ($cottage) => (int) $cottage->id === $selectedId);

            if ($selectedId > 0 && ! $selected) {
                flash('error', 'That cottage is not available for booking.');

                return redirect()->route('booking');
            }

            if ($selected) {
                $unavailable = Booking::where('cottage_id', $selected->id)
                    ->where('status', 'approved')
                    ->where('check_out', '>=', now()->toDateString())
                    ->orderBy('check_in')
                    ->get(['check_in', 'check_out', 'check_in_time', 'check_out_time'])
                    ->map(fn ($booking) => [
                        'check_in' => date_string($booking->check_in),
                        'check_out' => date_string($booking->check_out),
                        'check_in_time' => $booking->check_in_time ? substr((string) $booking->check_in_time, 0, 5) : '14:00',
                        'check_out_time' => $booking->check_out_time ? substr((string) $booking->check_out_time, 0, 5) : '12:00',
                    ])
                    ->all();
            }
        }

        $item = $selectedRoom ?: $selected;

        return view('booking', [
            'cottages' => $cottages,
            'rooms' => $rooms,
            'type' => $type,
            'selected' => $selected,
            'selectedRoom' => $selectedRoom,
            'unavailable' => $unavailable,
            'pageTitle' => $item ? 'Book ' . $item->name : 'Booking',
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $cottageId = (int) $request->input('cottage_id', 0);
        $roomId = (int) $request->input('room_id', 0);
        $isRoom = $roomId > 0;
        $checkIn = (string) $request->input('check_in', '');
        $checkOut = (string) $request->input('check_out', '');
        $checkInTime = $this->normalize_time($request->input('check_in_time'), '14:00');
        $checkOutTime = $this->normalize_time($request->input('check_out_time'), '12:00');
        $adults = (int) $request->input('adults', 0);
        $children = (int) $request->input('children', 0);
        $requests = trim((string) $request->input('special_requests', ''));

        if ($cottageId <= 0 && $roomId <= 0) {
            return $this->bookingFail(0, 'Choose a cottage or room before booking.');
        }
        if (! is_valid_date($checkIn) || ! is_valid_date($checkOut)) {
            return $this->bookingFail($cottageId, 'Please choose valid check-in and check-out dates.');
        }
        if ($checkIn < now()->toDateString()) {
            return $this->bookingFail($cottageId, 'The check-in date cannot be in the past.');
        }
        $startAt = new \DateTimeImmutable($checkIn . ' ' . $checkInTime);
        $endAt = new \DateTimeImmutable($checkOut . ' ' . $checkOutTime);
        if ($endAt <= $startAt) {
            return $this->bookingFail(
                $cottageId,
                $checkIn === $checkOut
                    ? 'For same-day bookings, choose a check-out time later than the check-in time.'
                    : 'The check-out date and time must be after the check-in date and time.'
            );
        }
        if ($adults < 1 || $adults > 20 || $children < 0 || $children > 20) {
            return $this->bookingFail($cottageId, 'Please enter a valid number of guests.');
        }
        if (mb_strlen($requests) > 1000) {
            return $this->bookingFail($cottageId, 'Special requests must be 1,000 characters or fewer.');
        }
        $nights = nights_between($checkIn, $checkOut);
        if ($nights > (int) config('booking.max_stay_nights')) {
            return $this->bookingFail($cottageId, 'Stays are limited to ' . config('booking.max_stay_nights') . ' nights.');
        }

        try {
            $receipt = DB::transaction(function () use ($cottageId, $roomId, $isRoom, $checkIn, $checkOut, $checkInTime, $checkOutTime, $adults, $children, $requests, $nights, $user) {
                if ($isRoom) {
                    $item = Room::where('id', $roomId)->where('status', 'active')->lockForUpdate()->first();

                    if (! $item) {
                        throw new \RuntimeException('That room is not available for booking.');
                    }
                } else {
                    $item = Cottage::where('id', $cottageId)->where('status', 'active')->lockForUpdate()->first();

                    if (! $item) {
                        throw new \RuntimeException('That cottage is not available for booking.');
                    }
                }

                $guests = $adults + $children;
                if ($guests > (int) $item->capacity) {
                    throw new \RuntimeException('This ' . ($isRoom ? 'room' : 'cottage') . ' holds up to ' . (int) $item->capacity . ' guests.');
                }

                $conflict = Booking::where($isRoom ? 'room_id' : 'cottage_id', $isRoom ? $roomId : $cottageId)
                    ->where('status', 'approved')
                    ->where('check_in', '<=', $checkOut)
                    ->where('check_out', '>=', $checkIn)
                    ->exists();

                if ($conflict) {
                    throw new \RuntimeException('Those dates are already reserved. Please choose another date.');
                }

                $receipt = generate_receipt_no();
                $entrance = $adults * (float) config('booking.entrance_fee_per_adult');
                $total = ((float) $item->rate_per_day * $nights) + $entrance;

                Booking::create([
                    'receipt_no' => $receipt,
                    'user_id' => $user->id,
                    'cottage_id' => $isRoom ? null : $cottageId,
                    'room_id' => $isRoom ? $roomId : null,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime,
                    'adults' => $adults,
                    'children' => $children,
                    'special_requests' => $requests !== '' ? $requests : null,
                    'entrance_fee' => $entrance,
                    'total_amount' => $total,
                    'payment_method' => 'pay_at_resort',
                    'status' => 'pending',
                ]);

                return $receipt;
            });

            flash('success', 'Booking submitted. Receipt #' . $receipt . ' is pending admin approval.');

            return redirect('/profile?tab=bookings');
        } catch (\RuntimeException $e) {
            return $this->bookingFail($cottageId, $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return $this->bookingFail($cottageId, 'We could not save your booking. Please try again.');
        }
    }

    public function cancel(Request $request)
    {
        $user = auth()->user();
        $bookingId = (int) $request->input('booking_id', 0);

        if ($bookingId <= 0) {
            flash('error', 'Choose a valid booking to cancel.');

            return redirect('/profile?tab=bookings');
        }

        try {
            DB::transaction(function () use ($bookingId, $user) {
                $booking = Booking::where('id', $bookingId)->where('user_id', $user->id)->lockForUpdate()->first();

                if (! $booking) {
                    throw new \RuntimeException('Booking not found.');
                }
                if (! in_array($booking->status, ['pending', 'approved'], true)) {
                    throw new \RuntimeException('This booking can no longer be cancelled.');
                }

                Booking::where('id', $bookingId)
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->update(['status' => 'cancelled']);
            });

            $receipt = Booking::where('id', $bookingId)->value('receipt_no');
            flash('success', 'Booking #' . $receipt . ' was cancelled.');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            flash('error', 'We could not cancel this booking. Please try again.');
        }

        return redirect('/profile?tab=bookings');
    }

    private function normalize_time(mixed $value, string $fallback): string
    {
        $value = is_string($value) ? trim($value) : '';

        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) === 1 ? $value : $fallback;
    }

    private function bookingFail(int $cottageId, string $message)
    {
        flash('error', $message);

        $roomId = (int) request()->input('room_id', 0);

        if ($roomId > 0) {
            return redirect('/booking?room=' . $roomId);
        }

        return redirect($cottageId > 0 ? '/booking?cottage=' . $cottageId : '/booking');
    }
}
