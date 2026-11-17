<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleCottagesSeeder extends Seeder
{
    public function run(): void
    {
        $cottages = [
            ['code' => 'CTG-0222', 'owner_id' => 3, 'name' => 'Cottage 1', 'location' => 'Poolside', 'description' => 'A breezy bamboo cottage a few steps from the shore. Wake up to the sound of the waves and watch the sunset from your private veranda.', 'capacity' => 4, 'amenities' => 'Air conditioning, Private veranda, Wi-Fi, Hot shower, Beach access', 'rate_per_day' => 500.00, 'status' => 'active', 'photo_count' => 2],
            ['code' => 'CTG-0223', 'owner_id' => 3, 'name' => 'Cottage 2', 'location' => 'Poolside', 'description' => 'Tucked between palms and flowering shrubs, this quiet cottage is perfect for couples and small families who want shade and privacy.', 'capacity' => 4, 'amenities' => 'Air conditioning, Queen bed, Wi-Fi, Coffee corner, Garden patio', 'rate_per_day' => 650.00, 'status' => 'active', 'photo_count' => 1],
            ['code' => 'CTG-0224', 'owner_id' => 3, 'name' => 'Cottage 3', 'location' => 'Poolside', 'description' => 'Our largest cottage, built for groups. Two sleeping areas, a long dining table, and direct access to the resort pool.', 'capacity' => 8, 'amenities' => 'Air conditioning, Two bedrooms, Wi-Fi, Dining area, Pool access', 'rate_per_day' => 900.00, 'status' => 'active', 'photo_count' => 1],
            ['code' => 'CTG-0225', 'owner_id' => 4, 'name' => 'Sunset Nipa Hut', 'location' => 'West Shore', 'description' => 'A traditional nipa hut facing the western shoreline. Awaiting admin approval before it can be booked.', 'capacity' => 2, 'amenities' => 'Fan cooled, Sea view, Hammock', 'rate_per_day' => 750.00, 'status' => 'pending', 'photo_count' => 1],
            ['code' => 'CTG-0226', 'owner_id' => 3, 'name' => 'Cottage 4', 'location' => 'Poolside', 'description' => 'A bright two-bedroom villa with a sea-facing balcony and direct beach access. Wake up to the sound of the waves and unwind on the veranda as the sun goes down.', 'capacity' => 6, 'amenities' => 'Air conditioning, Two bedrooms, Sea-facing balcony, Wi-Fi, Mini bar, Beach access', 'rate_per_day' => 1200.00, 'status' => 'active', 'photo_count' => 1],
            ['code' => 'CTG-0227', 'owner_id' => 3, 'name' => 'Cottage 5', 'location' => 'Poolside', 'description' => 'A cozy wooden cabin shaded by tall palms, with a garden patio and hammock. Ideal for couples and small families who want a quiet, leafy escape.', 'capacity' => 4, 'amenities' => 'Air conditioning, Queen bed, Wi-Fi, Outdoor shower, Hammock, Garden patio', 'rate_per_day' => 700.00, 'status' => 'active', 'photo_count' => 1],
            ['code' => 'CTG-0228', 'owner_id' => 3, 'name' => 'Cottage 6', 'location' => 'Poolside', 'description' => 'A private hideaway perched on the cliff edge with an unobstructed view of the sea. The terrace is the best seat in the resort for sunset.', 'capacity' => 2, 'amenities' => 'Air conditioning, King bed, Wi-Fi, Private terrace, Sunset view, Coffee corner', 'rate_per_day' => 850.00, 'status' => 'active', 'photo_count' => 1],
        ];

        DB::transaction(function () use ($cottages): void {
            foreach ($cottages as $cottage) {
                $photoCount = $cottage['photo_count'];
                unset($cottage['photo_count']);
                $requestedOwnerId = $cottage['owner_id'];
                $cottage['owner_id'] = DB::table('users')->where('id', $requestedOwnerId)->exists() ? $requestedOwnerId : null;
                $cottageId = DB::table('cottages')->where('code', $cottage['code'])->value('id');
                if ($cottageId === null) {
                    $cottageId = DB::table('cottages')->insertGetId($cottage);
                }
                if (! DB::table('cottage_photos')->where('cottage_id', $cottageId)->exists()) {
                    $photos = [];
                    for ($sortOrder = 0; $sortOrder < $photoCount; $sortOrder++) {
                        $photos[] = ['cottage_id' => $cottageId, 'path' => '/assets/img/cottage-photo.jpg', 'sort_order' => $sortOrder];
                    }
                    DB::table('cottage_photos')->insert($photos);
                }
            }
        });
    }
}
