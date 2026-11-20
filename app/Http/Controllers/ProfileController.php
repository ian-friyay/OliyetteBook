<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Cottage;
use App\Models\CottagePhoto;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $role = $user->role;

        $allowedTabs = ['dashboard', 'settings'];
        if ($role === 'guest') {
            $allowedTabs[] = 'bookings';
        }
        if ($role === 'owner') {
            $allowedTabs[] = 'cottages';
            $allowedTabs[] = 'rooms';
            $allowedTabs[] = 'efficiency';
        }

        $tab = (string) $request->query('tab', 'dashboard');
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'dashboard';
        }

        $settingsErrors = [];
        $passwordErrors = [];
        $cottageErrors = [];
        $roomErrors = [];

        $editId = (int) ($request->input('cottage_id') ?? $request->query('edit', 0));
        $editCottage = null;

        if ($role === 'owner' && $editId > 0) {
            $editCottage = Cottage::where('id', $editId)->where('owner_id', $userId)->first();

            if (! $editCottage && ! $request->isMethod('post')) {
                flash('error', 'That cottage could not be found.');

                return redirect('/profile?tab=cottages');
            }
        }

        $editRoomId = (int) ($request->input('room_id') ?? $request->query('edit_room', 0));
        $editRoom = null;

        if ($role === 'owner' && $editRoomId > 0) {
            $editRoom = Room::where('id', $editRoomId)->first();

            if (! $editRoom && ! $request->isMethod('post')) {
                flash('error', 'That room could not be found.');

                return redirect('/profile?tab=rooms');
            }
        }

        $cottageForm = [
            'id' => $editCottage ? (int) $editCottage->id : 0,
            'name' => $editCottage->name ?? '',
            'location' => $editCottage->location ?? '',
            'description' => $editCottage->description ?? '',
            'capacity' => $editCottage->capacity ?? 4,
            'amenities' => $editCottage->amenities ?? '',
            'rate_per_day' => $editCottage->rate_per_day ?? '',
            'photo' => '',
        ];

        if ($editCottage) {
            $cottageForm['photo'] = (string) (CottagePhoto::where('cottage_id', $editCottage->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->value('path') ?: '');
        }

        $roomForm = [
            'id' => $editRoom ? (int) $editRoom->id : 0,
            'name' => $editRoom->name ?? '',
            'location' => $editRoom->location ?? '',
            'description' => $editRoom->description ?? '',
            'capacity' => $editRoom->capacity ?? 2,
            'amenities' => $editRoom->amenities ?? '',
            'rate_per_day' => $editRoom->rate_per_day ?? '',
            'status' => $editRoom->status ?? 'active',
            'photo' => (string) ($editRoom->photo ?? ''),
        ];

        if ($request->isMethod('post')) {
            $form = (string) $request->input('form', '');

            if ($form === 'profile') {
                $firstName = trim((string) $request->input('first_name', ''));
                $lastName = trim((string) $request->input('last_name', ''));
                $phone = trim((string) $request->input('phone', ''));
                $phoneDigits = preg_replace('/\D+/', '', $phone) ?? '';

                if ($firstName === '' || mb_strlen($firstName) > 60) {
                    $settingsErrors['first_name'] = 'Enter your first name.';
                }
                if ($lastName === '' || mb_strlen($lastName) > 60) {
                    $settingsErrors['last_name'] = 'Enter your last name.';
                }
                if (! preg_match('/^09\d{9}$/', $phoneDigits)) {
                    $settingsErrors['phone'] = 'Use the format 09XX XXX XXXX.';
                }

                if (! $settingsErrors) {
                    User::where('id', $userId)->update([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $phoneDigits,
                    ]);

                    flash('success', 'Your profile details were updated.');

                    return redirect('/profile?tab=settings');
                }
                $tab = 'settings';
            }

            if ($form === 'password') {
                $current = (string) $request->input('current_password', '');
                $new = (string) $request->input('new_password', '');
                $confirm = (string) $request->input('confirm_password', '');

                $hash = (string) User::where('id', $userId)->value('password_hash');

                if (! password_verify($current, $hash)) {
                    $passwordErrors['current_password'] = 'Your current password is not correct.';
                }
                if (strlen($new) < 8) {
                    $passwordErrors['new_password'] = 'Use at least 8 characters.';
                }
                if ($new !== $confirm) {
                    $passwordErrors['confirm_password'] = 'The new passwords do not match.';
                }

                if (! $passwordErrors) {
                    User::where('id', $userId)->update([
                        'password_hash' => password_hash($new, PASSWORD_DEFAULT),
                    ]);
                    $request->session()->regenerate();
                    flash('success', 'Your password was changed.');

                    return redirect('/profile?tab=settings');
                }
                $tab = 'settings';
            }

            if ($form === 'cottage') {
                if ($role !== 'owner') {
                    abort(403, 'Access denied.');
                }

                $cottageForm = [
                    'id' => (int) $request->input('cottage_id', 0),
                    'name' => trim((string) $request->input('name', '')),
                    'location' => trim((string) $request->input('location', '')),
                    'description' => trim((string) $request->input('description', '')),
                    'capacity' => (int) $request->input('capacity', 0),
                    'amenities' => trim((string) $request->input('amenities', '')),
                    'rate_per_day' => trim((string) $request->input('rate_per_day', '')),
                    'photo' => $cottageForm['photo'],
                ];

                if ($cottageForm['id'] > 0 && ! $editCottage) {
                    $cottageErrors['form'] = 'That cottage does not belong to your account.';
                }
                if ($cottageForm['name'] === '' || mb_strlen($cottageForm['name']) > 100) {
                    $cottageErrors['name'] = 'Enter a cottage name (100 characters or fewer).';
                }
                if (mb_strlen($cottageForm['location']) > 100) {
                    $cottageErrors['location'] = 'Keep the location under 100 characters.';
                }
                if ($cottageForm['capacity'] < 1 || $cottageForm['capacity'] > 50) {
                    $cottageErrors['capacity'] = 'Capacity must be between 1 and 50 guests.';
                }
                $rate = (float) str_replace(',', '', $cottageForm['rate_per_day']);
                if ($rate <= 0) {
                    $cottageErrors['rate_per_day'] = 'Enter a daily rate greater than zero.';
                }

                $newPhotoPath = null;
                if (! $cottageErrors) {
                    try {
                        $newPhotoPath = save_uploaded_photo($request->file('photo'), 'cottage');
                    } catch (\RuntimeException $e) {
                        $cottageErrors['photo'] = $e->getMessage();
                    }
                }

                if (! $cottageErrors) {
                    try {
                        $oldPhoto = null;

                        DB::transaction(function () use (&$oldPhoto, $cottageForm, $rate, $newPhotoPath, $userId) {
                            if ($cottageForm['id'] > 0) {
                                Cottage::where('id', $cottageForm['id'])->where('owner_id', $userId)->update([
                                    'name' => $cottageForm['name'],
                                    'location' => $cottageForm['location'] !== '' ? $cottageForm['location'] : null,
                                    'description' => $cottageForm['description'] !== '' ? $cottageForm['description'] : null,
                                    'capacity' => $cottageForm['capacity'],
                                    'amenities' => $cottageForm['amenities'] !== '' ? $cottageForm['amenities'] : null,
                                    'rate_per_day' => $rate,
                                ]);
                                $cottageId = $cottageForm['id'];

                                $existing = CottagePhoto::where('cottage_id', $cottageId)
                                    ->orderBy('sort_order')
                                    ->orderBy('id')
                                    ->first();
                            } else {
                                $cottage = Cottage::create([
                                    'code' => generate_cottage_code(),
                                    'owner_id' => $userId,
                                    'name' => $cottageForm['name'],
                                    'location' => $cottageForm['location'] !== '' ? $cottageForm['location'] : null,
                                    'description' => $cottageForm['description'] !== '' ? $cottageForm['description'] : null,
                                    'capacity' => $cottageForm['capacity'],
                                    'amenities' => $cottageForm['amenities'] !== '' ? $cottageForm['amenities'] : null,
                                    'rate_per_day' => $rate,
                                    'status' => 'pending',
                                ]);
                                $cottageId = (int) $cottage->id;
                                $existing = null;
                            }

                            if ($newPhotoPath !== null) {
                                if ($existing) {
                                    $oldPhoto = $existing->path;
                                    CottagePhoto::where('id', $existing->id)->update(['path' => $newPhotoPath]);
                                } else {
                                    CottagePhoto::create([
                                        'cottage_id' => $cottageId,
                                        'path' => $newPhotoPath,
                                        'sort_order' => 0,
                                    ]);
                                }
                            }
                        });

                        remove_uploaded_photo($oldPhoto);

                        flash('success', $cottageForm['id'] > 0
                            ? 'Cottage details were updated.'
                            : 'Cottage submitted. It will appear on the Home page after admin approval.');

                        return redirect('/profile?tab=cottages');
                    } catch (\Throwable $e) {
                        remove_uploaded_photo($newPhotoPath);
                        report($e);
                        $cottageErrors['form'] = 'We could not save the cottage. Please try again.';
                    }
                }

                $tab = 'cottages';
            }

            if ($form === 'room') {
                if ($role !== 'owner') {
                    abort(403, 'Access denied.');
                }

                $roomForm = [
                    'id' => (int) $request->input('room_id', 0),
                    'name' => trim((string) $request->input('name', '')),
                    'location' => trim((string) $request->input('location', '')),
                    'description' => trim((string) $request->input('description', '')),
                    'capacity' => (int) $request->input('capacity', 0),
                    'amenities' => trim((string) $request->input('amenities', '')),
                    'rate_per_day' => trim((string) $request->input('rate_per_day', '')),
                    'status' => (string) $request->input('status', 'active'),
                    'photo' => $roomForm['photo'],
                ];

                if ($roomForm['id'] > 0 && ! $editRoom) {
                    $roomErrors['form'] = 'That room could not be found.';
                }
                if ($roomForm['name'] === '' || mb_strlen($roomForm['name']) > 100) {
                    $roomErrors['name'] = 'Enter a room name (100 characters or fewer).';
                }
                if (mb_strlen($roomForm['location']) > 100) {
                    $roomErrors['location'] = 'Keep the location under 100 characters.';
                }
                if ($roomForm['capacity'] < 1 || $roomForm['capacity'] > 50) {
                    $roomErrors['capacity'] = 'Capacity must be between 1 and 50 guests.';
                }
                $rate = (float) str_replace(',', '', $roomForm['rate_per_day']);
                if ($rate <= 0) {
                    $roomErrors['rate_per_day'] = 'Enter a daily rate greater than zero.';
                }
                if (! in_array($roomForm['status'], ['active', 'hidden'], true)) {
                    $roomForm['status'] = 'active';
                }

                $newPhotoPath = null;
                if (! $roomErrors) {
                    try {
                        $newPhotoPath = save_uploaded_photo($request->file('photo'), 'room');
                    } catch (\RuntimeException $e) {
                        $roomErrors['photo'] = $e->getMessage();
                    }
                }

                if (! $roomErrors) {
                    try {
                        $oldPhoto = null;
                        $status = $roomForm['status'];

                        DB::transaction(function () use (&$oldPhoto, $roomForm, $rate, $newPhotoPath, $status) {
                            if ($roomForm['id'] > 0) {
                                $room = Room::where('id', $roomForm['id'])->first();

                                if (! $room) {
                                    throw new \RuntimeException('That room could not be found.');
                                }

                                $oldPhoto = $room->photo;
                                $room->update([
                                    'name' => $roomForm['name'],
                                    'location' => $roomForm['location'] !== '' ? $roomForm['location'] : null,
                                    'description' => $roomForm['description'] !== '' ? $roomForm['description'] : null,
                                    'capacity' => $roomForm['capacity'],
                                    'amenities' => $roomForm['amenities'] !== '' ? $roomForm['amenities'] : null,
                                    'rate_per_day' => $rate,
                                    'photo' => $newPhotoPath ?? $room->photo,
                                    'status' => $status,
                                ]);
                            } else {
                                Room::create([
                                    'code' => generate_room_code(),
                                    'name' => $roomForm['name'],
                                    'location' => $roomForm['location'] !== '' ? $roomForm['location'] : null,
                                    'description' => $roomForm['description'] !== '' ? $roomForm['description'] : null,
                                    'capacity' => $roomForm['capacity'],
                                    'amenities' => $roomForm['amenities'] !== '' ? $roomForm['amenities'] : null,
                                    'rate_per_day' => $rate,
                                    'photo' => $newPhotoPath,
                                    'status' => $status,
                                ]);
                            }
                        });

                        if ($newPhotoPath !== null) {
                            remove_uploaded_photo($oldPhoto);
                        }

                        flash('success', $roomForm['id'] > 0
                            ? 'Room details were updated.'
                            : 'Room added. It is now available on the booking page.');

                        return redirect('/profile?tab=rooms');
                    } catch (\Throwable $e) {
                        remove_uploaded_photo($newPhotoPath);
                        report($e);
                        $roomErrors['form'] = 'We could not save the room. Please try again.';
                    }
                }

                $tab = 'rooms';
            }
        }

        $profile = User::find($userId) ?: $user;

        $bookings = DB::table('bookings as b')
            ->leftJoin('cottages as c', 'c.id', '=', 'b.cottage_id')
            ->leftJoin('rooms as rm', 'rm.id', '=', 'b.room_id')
            ->leftJoin('reviews as r', 'r.booking_id', '=', 'b.id')
            ->where('b.user_id', $userId)
            ->select('b.*')
            ->selectRaw('COALESCE(c.name, rm.name) AS cottage_name')
            ->selectRaw('COALESCE(c.location, rm.location) AS cottage_location')
            ->selectRaw('COALESCE(c.capacity, rm.capacity) AS capacity')
            ->selectRaw('COALESCE((SELECT path FROM cottage_photos p WHERE p.cottage_id = c.id ORDER BY sort_order, id LIMIT 1), rm.photo) AS photo')
            ->addSelect('r.id as review_id', 'r.status as review_status')
            ->orderByDesc('b.created_at')
            ->orderByDesc('b.id')
            ->get();

        $stats = [
            'total' => count($bookings),
            'pending' => 0,
            'approved' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'spent' => 0.0,
        ];

        $today = now()->toDateString();
        $upcoming = [];

        foreach ($bookings as $booking) {
            if (isset($stats[$booking->status])) {
                $stats[$booking->status]++;
            }
            if (in_array($booking->status, ['approved', 'completed'], true)) {
                $stats['spent'] += (float) $booking->total_amount;
            }
            if (in_array($booking->status, ['pending', 'approved'], true) && date_string($booking->check_in) >= $today) {
                $upcoming[] = $booking;
            }
        }

        usort($upcoming, fn ($a, $b) => strcmp((string) date_string($a->check_in), (string) date_string($b->check_in)));
        $nextStay = $upcoming[0] ?? null;

        $ownerCottages = collect();
        $ownerRooms = collect();
        $ownerStats = [
            'total' => 0,
            'active' => 0,
            'bookings' => 0,
            'revenue' => 0.0,
            'rating' => 0.0,
        ];
        $months = [];

        if ($role === 'owner') {
            $ownerCottages = DB::table('cottages as c')
                ->where('c.owner_id', $userId)
                ->select('c.*')
                ->selectRaw('(SELECT path FROM cottage_photos p WHERE p.cottage_id = c.id ORDER BY sort_order, id LIMIT 1) AS photo')
                ->selectRaw('(SELECT COUNT(*) FROM bookings b WHERE b.cottage_id = c.id) AS booking_count')
                ->selectRaw("(SELECT COALESCE(SUM(b.total_amount), 0) FROM bookings b WHERE b.cottage_id = c.id AND b.status IN ('approved','completed')) AS revenue")
                ->selectRaw('COALESCE(AVG(r.rating), 0) AS avg_rating')
                ->selectRaw('COUNT(r.id) AS review_count')
                ->leftJoin('reviews as r', function ($join) {
                    $join->on('r.cottage_id', '=', 'c.id')->where('r.status', '=', 'approved');
                })
                ->groupBy('c.id')
                ->orderByDesc('c.id')
                ->get();

            $ownerRooms = DB::table('rooms as rm')
                ->select('rm.*')
                ->selectRaw('(SELECT COUNT(*) FROM bookings b WHERE b.room_id = rm.id) AS booking_count')
                ->orderByDesc('rm.id')
                ->get();

            $rated = [];
            foreach ($ownerCottages as $cottage) {
                $ownerStats['total']++;
                if ($cottage->status === 'active') {
                    $ownerStats['active']++;
                }
                $ownerStats['bookings'] += (int) $cottage->booking_count;
                $ownerStats['revenue'] += (float) $cottage->revenue;
                if ((int) $cottage->review_count > 0) {
                    $rated[] = (float) $cottage->avg_rating;
                }
            }
            if ($rated) {
                $ownerStats['rating'] = array_sum($rated) / count($rated);
            }

            $monthStart = Carbon::now()->startOfMonth();
            for ($i = 5; $i >= 0; $i--) {
                $month = $monthStart->copy()->subMonths($i);
                $months[$month->format('Y-m')] = [
                    'label' => $month->format('M'),
                    'bookings' => 0,
                    'revenue' => 0.0,
                ];
            }

            $rows = DB::table('bookings as b')
                ->join('cottages as c', 'c.id', '=', 'b.cottage_id')
                ->where('c.owner_id', $userId)
                ->where('b.created_at', '>=', $monthStart->copy()->subMonths(5)->format('Y-m-d'))
                ->select('b.created_at', 'b.status', 'b.total_amount')
                ->get();

            foreach ($rows as $row) {
                $ym = date('Y-m', strtotime((string) $row->created_at));
                if (isset($months[$ym])) {
                    $months[$ym]['bookings']++;
                    if (in_array($row->status, ['approved', 'completed'], true)) {
                        $months[$ym]['revenue'] += (float) $row->total_amount;
                    }
                }
            }
        }

        $maxMonthBookings = 0;
        foreach ($months as $month) {
            $maxMonthBookings = max($maxMonthBookings, $month['bookings']);
        }
        $maxMonthBookings = max(1, $maxMonthBookings);

        $tabTitles = [
            'dashboard' => 'Dashboard',
            'bookings' => 'My Bookings',
            'cottages' => 'My Cottages',
            'rooms' => 'My Rooms',
            'efficiency' => 'Efficiency',
            'settings' => 'Settings',
        ];

        return view('profile', [
            'user' => $user,
            'profile' => $profile,
            'role' => $role,
            'tab' => $tab,
            'settingsErrors' => $settingsErrors,
            'passwordErrors' => $passwordErrors,
            'cottageErrors' => $cottageErrors,
            'cottageForm' => $cottageForm,
            'editCottage' => $editCottage,
            'roomErrors' => $roomErrors,
            'roomForm' => $roomForm,
            'editRoom' => $editRoom,
            'ownerRooms' => $ownerRooms,
            'bookings' => $bookings,
            'stats' => $stats,
            'today' => $today,
            'nextStay' => $nextStay,
            'ownerCottages' => $ownerCottages,
            'ownerStats' => $ownerStats,
            'months' => $months,
            'maxMonthBookings' => $maxMonthBookings,
            'pageTitle' => 'Profile · ' . ($tabTitles[$tab] ?? 'Dashboard'),
        ]);
    }
}
