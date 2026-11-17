<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class GalleryController extends Controller
{
    public function index()
    {
        $photos = DB::table('cottage_photos as p')
            ->join('cottages as c', 'c.id', '=', 'p.cottage_id')
            ->where('c.status', 'active')
            ->select('p.path', 'c.name', 'c.location')
            ->orderBy('c.id')
            ->orderBy('p.sort_order')
            ->orderBy('p.id')
            ->get();

        if ($photos->isEmpty()) {
            $photos = collect([
                ['path' => '/assets/img/cottage-1.svg', 'name' => 'Seaside Bamboo Cottage', 'location' => 'Beachfront'],
                ['path' => '/assets/img/cottage-2.svg', 'name' => 'Garden View Cottage', 'location' => 'Garden Wing'],
                ['path' => '/assets/img/cottage-3.svg', 'name' => 'Family Pavilion', 'location' => 'Poolside'],
            ])->map(fn ($photo) => (object) $photo);
        }

        return view('gallery', [
            'photos' => $photos,
            'pageTitle' => 'Gallery',
        ]);
    }
}
