<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        } else {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        foreach (['login_attempts', 'password_resets', 'feedback', 'inquiries', 'reviews', 'bookings', 'rooms', 'cottage_photos', 'cottages', 'users'] as $table) {
            DB::table($table)->truncate();
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        } else {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        DB::table('users')->insert([
            ['id' => 1, 'first_name' => 'Site', 'last_name' => 'Administrator', 'email' => 'admin@oliyettebook.test', 'phone' => '09170000001', 'password_hash' => password_hash('Admin@123', PASSWORD_DEFAULT), 'role' => 'admin', 'status' => 'active'],
            ['id' => 2, 'first_name' => 'Maria', 'last_name' => 'Santos', 'email' => 'maria@example.com', 'phone' => '09171234567', 'password_hash' => '$2y$10$A.NzNjCh2csiS1zp8JQRgevN6g5TcfVnY/SOjz.p2QnRuFwBV7ija', 'role' => 'guest', 'status' => 'active'],
            ['id' => 3, 'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'email' => 'juan@example.com', 'phone' => '09182345678', 'password_hash' => '$2y$10$SGlrHM91R.zOC.2u2EQwWe.rMW1sPgomAOplQ339J743MQ4.XQCRK', 'role' => 'owner', 'status' => 'active'],
            ['id' => 4, 'first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => 'ana@example.com', 'phone' => '09193456789', 'password_hash' => '$2y$10$SGlrHM91R.zOC.2u2EQwWe.rMW1sPgomAOplQ339J743MQ4.XQCRK', 'role' => 'owner', 'status' => 'pending'],
            ['id' => 5, 'first_name' => 'Pedro', 'last_name' => 'Penduko', 'email' => 'pedro@example.com', 'phone' => '09204567890', 'password_hash' => '$2y$10$A.NzNjCh2csiS1zp8JQRgevN6g5TcfVnY/SOjz.p2QnRuFwBV7ija', 'role' => 'guest', 'status' => 'active'],
        ]);

        DB::table('cottages')->insert([
            ['id' => 1, 'code' => 'CTG-0222', 'owner_id' => 3, 'name' => 'Cottage 1', 'location' => 'Poolside', 'description' => 'A breezy bamboo cottage a few steps from the shore. Wake up to the sound of the waves and watch the sunset from your private veranda.', 'capacity' => 4, 'amenities' => 'Air conditioning, Private veranda, Wi-Fi, Hot shower, Beach access', 'rate_per_day' => 500.00, 'status' => 'active'],
            ['id' => 2, 'code' => 'CTG-0223', 'owner_id' => 3, 'name' => 'Cottage 2', 'location' => 'Poolside', 'description' => 'Tucked between palms and flowering shrubs, this quiet cottage is perfect for couples and small families who want shade and privacy.', 'capacity' => 4, 'amenities' => 'Air conditioning, Queen bed, Wi-Fi, Coffee corner, Garden patio', 'rate_per_day' => 650.00, 'status' => 'active'],
            ['id' => 3, 'code' => 'CTG-0224', 'owner_id' => 3, 'name' => 'Cottage 3', 'location' => 'Poolside', 'description' => 'Our largest cottage, built for groups. Two sleeping areas, a long dining table, and direct access to the resort pool.', 'capacity' => 8, 'amenities' => 'Air conditioning, Two bedrooms, Wi-Fi, Dining area, Pool access', 'rate_per_day' => 900.00, 'status' => 'active'],
            ['id' => 4, 'code' => 'CTG-0225', 'owner_id' => 4, 'name' => 'Sunset Nipa Hut', 'location' => 'West Shore', 'description' => 'A traditional nipa hut facing the western shoreline. Awaiting admin approval before it can be booked.', 'capacity' => 2, 'amenities' => 'Fan cooled, Sea view, Hammock', 'rate_per_day' => 750.00, 'status' => 'pending'],
            ['id' => 5, 'code' => 'CTG-0226', 'owner_id' => 3, 'name' => 'Cottage 4', 'location' => 'Poolside', 'description' => 'A bright two-bedroom villa with a sea-facing balcony and direct beach access. Wake up to the sound of the waves and unwind on the veranda as the sun goes down.', 'capacity' => 6, 'amenities' => 'Air conditioning, Two bedrooms, Sea-facing balcony, Wi-Fi, Mini bar, Beach access', 'rate_per_day' => 1200.00, 'status' => 'active'],
            ['id' => 6, 'code' => 'CTG-0227', 'owner_id' => 3, 'name' => 'Cottage 5', 'location' => 'Poolside', 'description' => 'A cozy wooden cabin shaded by tall palms, with a garden patio and hammock. Ideal for couples and small families who want a quiet, leafy escape.', 'capacity' => 4, 'amenities' => 'Air conditioning, Queen bed, Wi-Fi, Outdoor shower, Hammock, Garden patio', 'rate_per_day' => 700.00, 'status' => 'active'],
            ['id' => 7, 'code' => 'CTG-0228', 'owner_id' => 3, 'name' => 'Cottage 6', 'location' => 'Poolside', 'description' => 'A private hideaway perched on the cliff edge with an unobstructed view of the sea. The terrace is the best seat in the resort for sunset.', 'capacity' => 2, 'amenities' => 'Air conditioning, King bed, Wi-Fi, Private terrace, Sunset view, Coffee corner', 'rate_per_day' => 850.00, 'status' => 'active'],
        ]);

        DB::table('cottage_photos')->insert([
            ['cottage_id' => 1, 'path' => '/assets/img/cottage-photo.jpg', 'sort_order' => 0],
            ['cottage_id' => 1, 'path' => '/assets/img/cottage-photo.jpg', 'sort_order' => 1],
            ['cottage_id' => 2, 'path' => '/assets/img/cottage-photo.jpg', 'sort_order' => 0],
            ['cottage_id' => 3, 'path' => '/assets/img/cottage-photo.jpg', 'sort_order' => 0],
            ['cottage_id' => 4, 'path' => '/assets/img/cottage-photo.jpg', 'sort_order' => 0],
            ['cottage_id' => 5, 'path' => '/assets/img/cottage-photo.jpg', 'sort_order' => 0],
            ['cottage_id' => 6, 'path' => '/assets/img/cottage-photo.jpg', 'sort_order' => 0],
            ['cottage_id' => 7, 'path' => '/assets/img/cottage-photo.jpg', 'sort_order' => 0],
        ]);

        DB::table('rooms')->insert([
            ['id' => 1, 'code' => 'RM-1001', 'name' => 'Room 1', 'location' => 'Pool Backside', 'description' => 'A bright air-conditioned room with a queen bed and a private balcony overlooking the sea.', 'capacity' => 4, 'amenities' => 'Air conditioning, Queen bed, Wi-Fi, Private balcony, Hot shower', 'rate_per_day' => 450.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['id' => 2, 'code' => 'RM-1002', 'name' => 'Room 2', 'location' => 'Pool Backside', 'description' => 'Two single beds opening onto the garden, ideal for friends travelling together.', 'capacity' => 4, 'amenities' => 'Air conditioning, Two single beds, Wi-Fi, Garden access', 'rate_per_day' => 400.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['id' => 3, 'code' => 'RM-1003', 'name' => 'Room 3', 'location' => 'Pool Backside', 'description' => 'A spacious suite with one queen bed, two single beds, and a sitting area for the whole family.', 'capacity' => 4, 'amenities' => 'Air conditioning, Queen bed, Two single beds, Wi-Fi, Sitting area', 'rate_per_day' => 800.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['id' => 4, 'code' => 'RM-1004', 'name' => 'Room 4', 'location' => 'Pool Backside', 'description' => 'A comfortable fan-cooled room with a double bed, perfect for short budget stays.', 'capacity' => 4, 'amenities' => 'Fan cooled, Double bed, Wi-Fi, Shared bathroom', 'rate_per_day' => 300.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['id' => 5, 'code' => 'RM-1005', 'name' => 'Room 5', 'location' => 'Pool Backside', 'description' => 'A spacious air-conditioned room with a king bed and a private terrace just steps from the sand.', 'capacity' => 4, 'amenities' => 'Air conditioning, King bed, Wi-Fi, Private terrace, Ocean view, Hot shower', 'rate_per_day' => 550.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
            ['id' => 6, 'code' => 'RM-1006', 'name' => 'Room 6', 'location' => 'Pool Backside', 'description' => 'A two-room suite with a king bed, a separate living area, and sweeping views of the resort.', 'capacity' => 4, 'amenities' => 'Air conditioning, King bed, Living area, Wi-Fi, Private balcony, Mini bar', 'rate_per_day' => 950.00, 'photo' => '/assets/img/room-photo.jpg', 'status' => 'active'],
        ]);

        DB::table('bookings')->insert([
            ['id' => 1, 'receipt_no' => '100213', 'user_id' => 2, 'cottage_id' => 1, 'check_in' => '2026-10-01', 'check_out' => '2026-10-03', 'adults' => 2, 'children' => 0, 'special_requests' => 'Extra towels please.', 'entrance_fee' => 200.00, 'total_amount' => 1200.00, 'payment_method' => 'pay_at_resort', 'status' => 'completed', 'created_at' => '2026-09-20 09:15:00'],
            ['id' => 2, 'receipt_no' => '100214', 'user_id' => 2, 'cottage_id' => 2, 'check_in' => '2026-11-20', 'check_out' => '2026-11-22', 'adults' => 2, 'children' => 1, 'special_requests' => 'Late check-in around 7 PM.', 'entrance_fee' => 200.00, 'total_amount' => 1500.00, 'payment_method' => 'gcash', 'status' => 'pending', 'created_at' => '2026-11-01 14:30:00'],
            ['id' => 3, 'receipt_no' => '100215', 'user_id' => 5, 'cottage_id' => 3, 'check_in' => '2026-11-10', 'check_out' => '2026-11-12', 'adults' => 4, 'children' => 2, 'special_requests' => 'Birthday celebration, cake delivery at 4 PM.', 'entrance_fee' => 400.00, 'total_amount' => 2200.00, 'payment_method' => 'gcash', 'status' => 'approved', 'created_at' => '2026-11-02 10:05:00'],
            ['id' => 4, 'receipt_no' => '100216', 'user_id' => 5, 'cottage_id' => 1, 'check_in' => '2026-09-05', 'check_out' => '2026-09-07', 'adults' => 2, 'children' => 0, 'special_requests' => null, 'entrance_fee' => 200.00, 'total_amount' => 1200.00, 'payment_method' => 'pay_at_resort', 'status' => 'completed', 'created_at' => '2026-08-30 16:20:00'],
            ['id' => 5, 'receipt_no' => '100217', 'user_id' => 5, 'cottage_id' => 2, 'check_in' => '2026-08-01', 'check_out' => '2026-08-02', 'adults' => 2, 'children' => 0, 'special_requests' => null, 'entrance_fee' => 200.00, 'total_amount' => 850.00, 'payment_method' => 'gcash', 'status' => 'completed', 'created_at' => '2026-07-25 11:00:00'],
            ['id' => 6, 'receipt_no' => '100218', 'user_id' => 2, 'cottage_id' => 3, 'check_in' => '2026-07-10', 'check_out' => '2026-07-12', 'adults' => 2, 'children' => 0, 'special_requests' => null, 'entrance_fee' => 200.00, 'total_amount' => 2000.00, 'payment_method' => 'pay_at_resort', 'status' => 'completed', 'created_at' => '2026-07-01 08:45:00'],
        ]);

        DB::table('reviews')->insert([
            ['id' => 1, 'booking_id' => 4, 'user_id' => 5, 'cottage_id' => 1, 'rating' => 5, 'comment' => 'The bamboo cottage was spotless and the sunset view was unreal. Staff were very accommodating.', 'status' => 'approved', 'created_at' => '2026-09-08 10:00:00'],
            ['id' => 2, 'booking_id' => 5, 'user_id' => 5, 'cottage_id' => 2, 'rating' => 4, 'comment' => 'Quiet and cool even in the afternoon. The garden patio is a great place for coffee.', 'status' => 'approved', 'created_at' => '2026-08-03 09:30:00'],
            ['id' => 3, 'booking_id' => 6, 'user_id' => 2, 'cottage_id' => 3, 'rating' => 5, 'comment' => 'Plenty of space for the whole family and the pool was just outside our door.', 'status' => 'pending', 'created_at' => '2026-07-13 15:10:00'],
        ]);

        DB::table('inquiries')->insert([
            ['name' => 'Liza Mercado', 'email' => 'liza@example.com', 'message' => 'Do you allow day tours for 10 people?', 'is_read' => 0],
        ]);
    }
}


