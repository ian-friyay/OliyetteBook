<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cottage;
use App\Models\CottagePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CottageController extends Controller
{
    public function index(Request $request)
    {
        $errors = [];
        $filter = (string) $request->query('status', 'all');
        $allowedFilters = ['all', 'pending', 'active', 'hidden'];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $editId = (int) ($request->query('edit') ?? $request->input('cottage_id', 0));
        $editCottage = null;
        $currentPhoto = '';

        if ($editId > 0) {
            $editCottage = Cottage::find($editId);

            if ($editCottage) {
                $currentPhoto = (string) (CottagePhoto::where('cottage_id', $editId)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->value('path') ?: '');
            } elseif (! $request->isMethod('post')) {
                flash('error', 'That cottage could not be found.');

                return redirect('/admin/cottages');
            }
        }

        $formValues = [
            'id' => $editCottage ? (int) $editCottage->id : 0,
            'name' => $editCottage->name ?? '',
            'owner_id' => $editCottage->owner_id ?? '',
            'location' => $editCottage->location ?? '',
            'description' => $editCottage->description ?? '',
            'capacity' => $editCottage->capacity ?? 4,
            'amenities' => $editCottage->amenities ?? '',
            'rate_per_day' => $editCottage->rate_per_day ?? '',
            'status' => $editCottage->status ?? 'pending',
        ];

        if ($request->isMethod('post')) {
            $form = (string) $request->input('form', '');

            if ($form === 'status') {
                $cottageId = (int) $request->input('cottage_id', 0);
                $status = (string) $request->input('status', '');
                if ($cottageId <= 0 || ! in_array($status, ['pending', 'active', 'hidden'], true)) {
                    flash('error', 'Invalid cottage status change.');
                } else {
                    DB::table('cottages')->where('id', $cottageId)->update(['status' => $status]);
                    flash('success', 'Cottage status changed to ' . status_label($status) . '.');
                }

                return redirect('/admin/cottages?status=' . urlencode($filter));
            }

            if ($form === 'cottage') {
                $formValues = [
                    'id' => (int) $request->input('cottage_id', 0),
                    'name' => trim((string) $request->input('name', '')),
                    'owner_id' => (string) $request->input('owner_id', ''),
                    'location' => trim((string) $request->input('location', '')),
                    'description' => trim((string) $request->input('description', '')),
                    'capacity' => (int) $request->input('capacity', 0),
                    'amenities' => trim((string) $request->input('amenities', '')),
                    'rate_per_day' => trim((string) $request->input('rate_per_day', '')),
                    'status' => (string) $request->input('status', 'pending'),
                ];

                if ($formValues['id'] > 0 && ! $editCottage) {
                    $errors['form'] = 'That cottage could not be found.';
                }
                if ($formValues['name'] === '' || mb_strlen($formValues['name']) > 100) {
                    $errors['name'] = 'Enter a cottage name (100 characters or fewer).';
                }
                if (mb_strlen($formValues['location']) > 100) {
                    $errors['location'] = 'Keep the location under 100 characters.';
                }
                if ($formValues['capacity'] < 1 || $formValues['capacity'] > 50) {
                    $errors['capacity'] = 'Capacity must be between 1 and 50 guests.';
                }
                $rate = (float) str_replace(',', '', $formValues['rate_per_day']);
                if ($rate <= 0) {
                    $errors['rate_per_day'] = 'Enter a daily rate greater than zero.';
                }
                if (! in_array($formValues['status'], ['pending', 'active', 'hidden'], true)) {
                    $errors['status'] = 'Choose a valid status.';
                }

                $ownerId = $formValues['owner_id'] !== '' ? (int) $formValues['owner_id'] : null;
                if ($ownerId !== null) {
                    $exists = DB::table('users')->where('id', $ownerId)->where('role', 'owner')->exists();
                    if (! $exists) {
                        $errors['owner_id'] = 'Choose a valid resort owner.';
                    }
                }

                $newPhotoPath = null;
                if (! $errors) {
                    try {
                        $newPhotoPath = save_uploaded_photo($request->file('photo'), 'cottage');
                    } catch (\RuntimeException $e) {
                        $errors['photo'] = $e->getMessage();
                    }
                }

                if (! $errors) {
                    try {
                        $oldPhoto = null;

                        DB::transaction(function () use (&$oldPhoto, $formValues, $rate, $ownerId, $newPhotoPath) {
                            if ($formValues['id'] > 0) {
                                Cottage::where('id', $formValues['id'])->update([
                                    'owner_id' => $ownerId,
                                    'name' => $formValues['name'],
                                    'location' => $formValues['location'] !== '' ? $formValues['location'] : null,
                                    'description' => $formValues['description'] !== '' ? $formValues['description'] : null,
                                    'capacity' => $formValues['capacity'],
                                    'amenities' => $formValues['amenities'] !== '' ? $formValues['amenities'] : null,
                                    'rate_per_day' => $rate,
                                    'status' => $formValues['status'],
                                ]);
                                $cottageId = $formValues['id'];

                                $existing = CottagePhoto::where('cottage_id', $cottageId)
                                    ->orderBy('sort_order')
                                    ->orderBy('id')
                                    ->first();
                            } else {
                                $cottage = Cottage::create([
                                    'code' => generate_cottage_code(),
                                    'owner_id' => $ownerId,
                                    'name' => $formValues['name'],
                                    'location' => $formValues['location'] !== '' ? $formValues['location'] : null,
                                    'description' => $formValues['description'] !== '' ? $formValues['description'] : null,
                                    'capacity' => $formValues['capacity'],
                                    'amenities' => $formValues['amenities'] !== '' ? $formValues['amenities'] : null,
                                    'rate_per_day' => $rate,
                                    'status' => $formValues['status'],
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
                        flash('success', $formValues['id'] > 0 ? 'Cottage updated.' : 'Cottage created.');

                        return redirect('/admin/cottages');
                    } catch (\Throwable $e) {
                        remove_uploaded_photo($newPhotoPath);
                        report($e);
                        $errors['form'] = 'We could not save the cottage. Please try again.';
                    }
                }
            }
        }

        $owners = DB::table('users')
            ->where('role', 'owner')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        $query = DB::table('cottages as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.owner_id')
            ->leftJoin('reviews as r', function ($join) {
                $join->on('r.cottage_id', '=', 'c.id')->where('r.status', '=', 'approved');
            })
            ->select('c.*', 'u.first_name', 'u.last_name')
            ->selectRaw('(SELECT path FROM cottage_photos p WHERE p.cottage_id = c.id ORDER BY sort_order, id LIMIT 1) AS photo')
            ->selectRaw('COALESCE(AVG(r.rating), 0) AS avg_rating')
            ->selectRaw('COUNT(r.id) AS review_count')
            ->selectRaw('(SELECT COUNT(*) FROM bookings b WHERE b.cottage_id = c.id) AS booking_count');

        if ($filter !== 'all') {
            $query->where('c.status', $filter);
        }

        $cottages = $query->groupBy('c.id')->orderByDesc('c.id')->get();

        return view('admin.cottages', [
            'errors' => $errors,
            'filter' => $filter,
            'formValues' => $formValues,
            'editCottage' => $editCottage,
            'currentPhoto' => $currentPhoto,
            'owners' => $owners,
            'cottages' => $cottages,
            'adminPage' => 'cottages',
            'pageTitle' => 'Cottages',
        ]);
    }
}
