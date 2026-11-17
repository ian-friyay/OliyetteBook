<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleRoomsSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['code' => 'RM-1001', 'name' => 'Room 1', 'location' => 'Pool Backside', 'description' => 'A bright air-conditioned room with a queen bed and a private balcony overlooking the sea.', 'capacity' => 4, 'amenities' => 'Air conditioning, Queen bed, Wi-Fi, Private balcony, Hot shower', 'rate_per_day' => 450.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['code' => 'RM-1002', 'name' => 'Room 2', 'location' => 'Pool Backside', 'description' => 'Two single beds opening onto the garden, ideal for friends travelling together.', 'capacity' => 4, 'amenities' => 'Air conditioning, Two single beds, Wi-Fi, Garden access', 'rate_per_day' => 400.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['code' => 'RM-1003', 'name' => 'Room 3', 'location' => 'Pool Backside', 'description' => 'A spacious suite with one queen bed, two single beds, and a sitting area for the whole family.', 'capacity' => 4, 'amenities' => 'Air conditioning, Queen bed, Two single beds, Wi-Fi, Sitting area', 'rate_per_day' => 800.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['code' => 'RM-1004', 'name' => 'Room 4', 'location' => 'Pool Backside', 'description' => 'A comfortable fan-cooled room with a double bed, perfect for short budget stays.', 'capacity' => 4, 'amenities' => 'Fan cooled, Double bed, Wi-Fi, Shared bathroom', 'rate_per_day' => 300.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['code' => 'RM-1005', 'name' => 'Room 5', 'location' => 'Pool Backside', 'description' => 'A spacious air-conditioned room with a king bed and a private terrace just steps from the sand.', 'capacity' => 4, 'amenities' => 'Air conditioning, King bed, Wi-Fi, Private terrace, Ocean view, Hot shower', 'rate_per_day' => 550.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['code' => 'RM-1006', 'name' => 'Room 6', 'location' => 'Pool Backside', 'description' => 'A two-room suite with a king bed, a separate living area, and sweeping views of the resort.', 'capacity' => 4, 'amenities' => 'Air conditioning, King bed, Living area, Wi-Fi, Private balcony, Mini bar', 'rate_per_day' => 950.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
        ];

        DB::transaction(function () use ($rooms): void {
            foreach ($rooms as $room) {
                if (! DB::table('rooms')->where('code', $room['code'])->exists()) {
                    DB::table('rooms')->insert($room);
                }
            }
        });
    }
}
