<?php

namespace Tests\Feature;

use Database\Seeders\SampleRoomsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tests\TestCase;

class SampleRoomsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_rooms_are_added_idempotently_without_seeding_accounts(): void
    {
        $this->seed(SampleRoomsSeeder::class);
        $this->seed(SampleRoomsSeeder::class);

        $this->assertSame(6, DB::table('rooms')->count());
        $this->assertSame(6, DB::table('rooms')->where('status', 'active')->count());
        $this->assertSame(0, DB::table('users')->count());

        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Guest',
            'email' => 'room-listing-test@example.com',
            'phone' => '09170000000',
            'password_hash' => Hash::make('test-password'),
            'role' => 'guest',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('booking', ['type' => 'rooms']))
            ->assertOk()
            ->assertSee('Room 1')
            ->assertSee('Room 6')
            ->assertSee('Select Room');
    }

    public function test_existing_rooms_are_not_overwritten_by_the_sample_seeder(): void
    {
        DB::table('rooms')->insert([
            'code' => 'RM-1001',
            'name' => 'Custom Room Name',
            'location' => 'Custom Location',
            'description' => 'Existing listing description',
            'capacity' => 2,
            'amenities' => 'Custom amenity',
            'rate_per_day' => 1000.00,
            'photo' => '/uploads/custom-room.jpg',
            'status' => 'hidden',
        ]);

        $this->seed(SampleRoomsSeeder::class);

        $this->assertDatabaseHas('rooms', [
            'code' => 'RM-1001',
            'name' => 'Custom Room Name',
            'location' => 'Custom Location',
            'rate_per_day' => 1000.00,
            'photo' => '/uploads/custom-room.jpg',
            'status' => 'hidden',
        ]);
        $this->assertSame(6, DB::table('rooms')->count());
    }
}
