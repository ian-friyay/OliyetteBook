<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cottage;
use App\Models\PasswordReset;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComprehensiveQATest extends TestCase
{
    use RefreshDatabase;

    // --- FR-01: Registration & Login ---

    public function test_TC_F01a_valid_guest_registration(): void
    {
        $response = $this->post('/signup', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'new01@example.com',
            'phone' => '09171234567',
            'role' => 'guest',
            'password' => 'Test@1234',
            'confirm_password' => 'Test@1234',
            'consent' => '1',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'new01@example.com',
            'first_name' => 'John',
            'role' => 'guest',
            'status' => 'active',
        ]);
    }

    public function test_TC_F01b_login_with_valid_credentials(): void
    {
        $user = $this->createTestUser('guest', 'guest01@example.com', 'Guest@1234');

        $response = $this->post('/login', [
            'email' => 'guest01@example.com',
            'password' => 'Guest@1234',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_TC_F01c_reject_duplicate_email_registration(): void
    {
        $this->createTestUser('admin', 'admin@oliyettebook.test', 'Admin@123');

        $response = $this->post('/signup', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'admin@oliyettebook.test',
            'phone' => '09171234567',
            'role' => 'guest',
            'password' => 'Test@1234',
            'confirm_password' => 'Test@1234',
            'consent' => '1',
        ]);

        $response->assertOk()->assertSee('An account with that email already exists.');
        $this->assertGuest();
    }

    public function test_TC_F01d_deny_login_with_incorrect_password(): void
    {
        $this->createTestUser('guest', 'guest01@example.com', 'Guest@1234');

        $response = $this->post('/login', [
            'email' => 'guest01@example.com',
            'password' => 'Wrong@9999',
        ]);

        $response->assertOk()->assertSee('Invalid credentials, or your account is not active yet.');
        $this->assertGuest();
    }

    public function test_TC_F01e_registration_validation_missing_fields(): void
    {
        $response = $this->post('/signup', [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'password' => '',
            'confirm_password' => '',
        ]);

        $response->assertOk()
            ->assertSee('Enter your first name')
            ->assertSee('Enter your last name')
            ->assertSee('Enter a valid email address')
            ->assertSee('Use at least 8 characters');
        $this->assertGuest();
    }

    public function test_TC_F01f_login_locks_after_consecutive_failed_attempts(): void
    {
        $this->createTestUser('guest', 'guest01@example.com', 'Guest@1234');

        // 5 consecutive failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'guest01@example.com',
                'password' => 'Wrong@9999',
            ]);
        }

        // 6th attempt is locked
        $response = $this->post('/login', [
            'email' => 'guest01@example.com',
            'password' => 'Wrong@9999',
        ]);

        $response->assertOk()->assertSee('Too many failed login attempts');
    }

    public function test_TC_F01g_owner_registration_pending_admin_approval(): void
    {
        $response = $this->post('/signup', [
            'first_name' => 'Alice',
            'last_name' => 'Owner',
            'email' => 'owner01@example.com',
            'phone' => '09181234567',
            'role' => 'owner',
            'password' => 'Owner@1234',
            'confirm_password' => 'Owner@1234',
            'consent' => '1',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'owner01@example.com',
            'role' => 'owner',
            'status' => 'pending',
        ]);

        $loginResponse = $this->post('/login', [
            'email' => 'owner01@example.com',
            'password' => 'Owner@1234',
        ]);
        $loginResponse->assertOk()->assertSee('waiting for admin approval');
    }

    public function test_TC_F01h_reject_invalid_email_and_short_password(): void
    {
        $response = $this->post('/signup', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'guest@@example',
            'phone' => '09171234567',
            'role' => 'guest',
            'password' => '123',
            'confirm_password' => '123',
            'consent' => '1',
        ]);

        $response->assertOk()
            ->assertSee('Enter a valid email address')
            ->assertSee('Use at least 8 characters');
    }

    // --- FR-02: View Accommodations ---

    public function test_TC_F02a_and_F02b_catalog_and_details_show_actual_capacity_and_rates(): void
    {
        $cottageId = $this->createCottageRecord('Luxury Cottage', 8, 2500);
        $roomId = $this->createRoomRecord('Sea Suite', 3, 1500);

        $response = $this->get('/');
        $response->assertOk()
            ->assertSee('Luxury Cottage')
            ->assertSee('Up to 8 guests');

        $guest = $this->createTestUser('guest', 'guest_cat@example.com', 'Pass@1234');
        $detailResponse = $this->actingAs($guest)->get('/booking?cottage=' . $cottageId);
        $detailResponse->assertOk()
            ->assertSee('Luxury Cottage')
            ->assertSee('8 guests')
            ->assertSee('2,500.00');

        $roomDetail = $this->actingAs($guest)->get('/booking?type=rooms&room=' . $roomId);
        $roomDetail->assertOk()
            ->assertSee('Sea Suite')
            ->assertSee('3 guests')
            ->assertSee('1,500.00');
    }

    // --- FR-03: Booking Calendar & Availability ---

    public function test_TC_F03c_and_F03d_calendar_date_validation(): void
    {
        $guest = $this->createTestUser('guest', 'guest_cal@example.com', 'Pass@1234');
        $cottageId = $this->createCottageRecord();

        // Past date
        $past = now()->subDays(5)->toDateString();
        $responsePast = $this->actingAs($guest)->post('/actions/book', [
            'cottage_id' => $cottageId,
            'check_in' => $past,
            'check_out' => now()->subDays(4)->toDateString(),
            'adults' => 2,
        ]);
        $responsePast->assertRedirect('/booking?cottage=' . $cottageId);

        // Check-out before check-in
        $responseInverted = $this->actingAs($guest)->post('/actions/book', [
            'cottage_id' => $cottageId,
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(5)->toDateString(),
            'adults' => 2,
        ]);
        $responseInverted->assertRedirect('/booking?cottage=' . $cottageId);
    }

    // --- FR-04: Online Reservation ---

    public function test_TC_F04b_capacity_limit_validation(): void
    {
        $guest = $this->createTestUser('guest', 'guest_cap@example.com', 'Pass@1234');
        $cottageId = $this->createCottageRecord('Small Cottage', 2, 500);

        $response = $this->actingAs($guest)->post('/actions/book', [
            'cottage_id' => $cottageId,
            'check_in' => now()->addDays(20)->toDateString(),
            'check_out' => now()->addDays(21)->toDateString(),
            'adults' => 3,
            'children' => 0,
        ]);

        $response->assertRedirect('/booking?cottage=' . $cottageId);
        $this->assertSame(0, DB::table('bookings')->where('cottage_id', $cottageId)->count());
    }

    public function test_TC_F04c_max_stay_nights_limit(): void
    {
        $guest = $this->createTestUser('guest', 'guest_stay@example.com', 'Pass@1234');
        $cottageId = $this->createCottageRecord();

        $response = $this->actingAs($guest)->post('/actions/book', [
            'cottage_id' => $cottageId,
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(40)->toDateString(),
            'adults' => 2,
            'children' => 0,
        ]);

        $response->assertRedirect('/booking?cottage=' . $cottageId);
        $this->assertSame(0, DB::table('bookings')->where('cottage_id', $cottageId)->count());
    }

    public function test_TC_F04f_entrance_fee_and_total_calculation(): void
    {
        $guest = $this->createTestUser('guest', 'guest_fee@example.com', 'Pass@1234');
        $cottageId = $this->createCottageRecord('Fee Cottage', 6, 1000);

        $checkIn = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(12)->toDateString(); // 2 nights

        $response = $this->actingAs($guest)->post('/actions/book', [
            'cottage_id' => $cottageId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 3, // 3 adults * 100 = 300 fee
            'children' => 1,
        ]);

        $response->assertRedirect('/profile?tab=bookings');
        $booking = DB::table('bookings')->where('cottage_id', $cottageId)->first();
        $this->assertNotNull($booking);
        $this->assertEquals(300.00, (float) $booking->entrance_fee);
        $this->assertEquals(2300.00, (float) $booking->total_amount); // 2 * 1000 + 300 = 2300
    }

    public function test_TC_F04g_room_reservation(): void
    {
        $guest = $this->createTestUser('guest', 'guest_rm@example.com', 'Pass@1234');
        $roomId = $this->createRoomRecord('Room Deluxe', 4, 800);

        $checkIn = now()->addDays(15)->toDateString();
        $checkOut = now()->addDays(16)->toDateString();

        $response = $this->actingAs($guest)->post('/actions/book', [
            'room_id' => $roomId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 2,
            'children' => 0,
        ]);

        $response->assertRedirect('/profile?tab=bookings');
        $this->assertDatabaseHas('bookings', [
            'room_id' => $roomId,
            'user_id' => $guest->id,
            'status' => 'pending',
        ]);
    }

    // --- FR-05 & FR-06: Booking History & Cancellation ---

    public function test_TC_F05a_and_F06a_b_c_booking_history_and_cancellation(): void
    {
        $guest = $this->createTestUser('guest', 'guest_hist@example.com', 'Pass@1234');
        $cottageId = $this->createCottageRecord();

        $pendingId = $this->createBookingRecord($guest->id, $cottageId, now()->addDays(10)->toDateString(), now()->addDays(11)->toDateString(), 'pending');
        $approvedId = $this->createBookingRecord($guest->id, $cottageId, now()->addDays(20)->toDateString(), now()->addDays(21)->toDateString(), 'approved');
        $completedId = $this->createBookingRecord($guest->id, $cottageId, now()->subDays(5)->toDateString(), now()->subDays(4)->toDateString(), 'completed');

        // View bookings
        $response = $this->actingAs($guest)->get('/profile?tab=bookings');
        $response->assertOk()
            ->assertSee('Pending')
            ->assertSee('Approved')
            ->assertSee('Completed');

        // Cancel pending
        $this->actingAs($guest)->post('/actions/cancel-booking', ['booking_id' => $pendingId])
            ->assertRedirect('/profile?tab=bookings');
        $this->assertDatabaseHas('bookings', ['id' => $pendingId, 'status' => 'cancelled']);

        // Cancel approved
        $this->actingAs($guest)->post('/actions/cancel-booking', ['booking_id' => $approvedId])
            ->assertRedirect('/profile?tab=bookings');
        $this->assertDatabaseHas('bookings', ['id' => $approvedId, 'status' => 'cancelled']);

        // Cannot cancel completed
        $this->actingAs($guest)->post('/actions/cancel-booking', ['booking_id' => $completedId])
            ->assertRedirect('/profile?tab=bookings');
        $this->assertDatabaseHas('bookings', ['id' => $completedId, 'status' => 'completed']);
    }

    // --- FR-07: Guest Profile Management ---

    public function test_TC_F07b_c_d_e_guest_profile_and_password_management(): void
    {
        $guest = $this->createTestUser('guest', 'guest_prof@example.com', 'OldPass@123');

        // Update profile
        $this->actingAs($guest)->post('/profile?tab=settings', [
            'form' => 'profile',
            'first_name' => 'UpdatedFirst',
            'last_name' => 'UpdatedLast',
            'phone' => '09199999999',
        ])->assertRedirect('/profile?tab=settings');

        $this->assertDatabaseHas('users', [
            'id' => $guest->id,
            'first_name' => 'UpdatedFirst',
            'last_name' => 'UpdatedLast',
            'phone' => '09199999999',
        ]);

        // Wrong current password
        $this->actingAs($guest)->post('/profile?tab=settings', [
            'form' => 'password',
            'current_password' => 'WrongCurrent@123',
            'new_password' => 'NewSecret@123',
            'confirm_password' => 'NewSecret@123',
        ])->assertOk()->assertSee('Your current password is not correct.');

        // Valid password change
        $this->actingAs($guest)->post('/profile?tab=settings', [
            'form' => 'password',
            'current_password' => 'OldPass@123',
            'new_password' => 'NewSecret@123',
            'confirm_password' => 'NewSecret@123',
        ])->assertRedirect('/profile?tab=settings');

        $this->assertTrue(password_verify('NewSecret@123', (string) $guest->fresh()->password_hash));
    }

    // --- FR-08 & FR-09: Inquiries & Feedback ---

    public function test_TC_F08a_submit_general_inquiry(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Inquiry Sender',
            'email' => 'sender@example.com',
            'message' => 'Do you have discounts for large corporate events?',
        ]);

        $response->assertRedirect('/contact');
        $this->assertDatabaseHas('inquiries', [
            'email' => 'sender@example.com',
            'message' => 'Do you have discounts for large corporate events?',
        ]);
    }

    public function test_TC_F09c_submit_guest_feedback(): void
    {
        $guest = $this->createTestUser('guest', 'guest_fb@example.com', 'Pass@1234');

        $response = $this->actingAs($guest)->post('/actions/submit-feedback', [
            'comment' => 'The resort ambiance is serene and well kept.',
        ]);

        $response->assertRedirect('/reviews');
        $this->assertDatabaseHas('feedback', [
            'user_id' => $guest->id,
            'comment' => 'The resort ambiance is serene and well kept.',
        ]);
    }

    // --- FR-10: Admin Dashboard ---

    public function test_TC_F10a_b_c_d_admin_dashboard_metrics_and_lists(): void
    {
        $admin = $this->createTestUser('admin', 'admin_dash@example.com', 'Admin@123');
        $guest = $this->createTestUser('guest', 'guest_dash@example.com', 'Pass@1234');
        $owner = $this->createTestUser('owner', 'owner_dash@example.com', 'Pass@1234', 'pending');
        $cottageId = $this->createCottageRecord();
        $this->createBookingRecord($guest->id, $cottageId, now()->addDays(5)->toDateString(), now()->addDays(6)->toDateString(), 'pending');

        DB::table('inquiries')->insert([
            'name' => 'Curious Visitor',
            'email' => 'visitor@example.com',
            'message' => 'Is pool open at night?',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertOk()
            ->assertSee('Admin Dashboard')
            ->assertSee('owner_dash@example.com')
            ->assertSee('Is pool open at night?');
    }

    // --- FR-11: Cottage & Room Management ---

    public function test_TC_F11a_b_c_d_admin_cottage_management(): void
    {
        $admin = $this->createTestUser('admin', 'admin_ctg@example.com', 'Admin@123');
        $cottageId = $this->createCottageRecord('Editable Cottage', 4, 1200);

        // View list
        $this->actingAs($admin)->get('/admin/cottages')->assertOk()->assertSee('Editable Cottage');

        // Edit cottage
        $this->actingAs($admin)->post('/admin/cottages', [
            'form' => 'cottage',
            'cottage_id' => $cottageId,
            'name' => 'Renamed Cottage',
            'location' => 'Beachfront',
            'capacity' => 6,
            'rate_per_day' => '1500',
            'status' => 'active',
        ])->assertRedirect('/admin/cottages');

        $this->assertDatabaseHas('cottages', [
            'id' => $cottageId,
            'name' => 'Renamed Cottage',
            'capacity' => 6,
            'rate_per_day' => 1500,
        ]);

        // Hide cottage
        $this->actingAs($admin)->post('/admin/cottages', [
            'form' => 'status',
            'cottage_id' => $cottageId,
            'status' => 'hidden',
        ])->assertRedirect('/admin/cottages?status=all');

        $this->assertDatabaseHas('cottages', [
            'id' => $cottageId,
            'status' => 'hidden',
        ]);
    }

    // --- FR-13: User Management ---

    public function test_TC_F13a_b_admin_user_management(): void
    {
        $admin = $this->createTestUser('admin', 'admin_usr@example.com', 'Admin@123');
        $guest = $this->createTestUser('guest', 'target_guest@example.com', 'Pass@1234');

        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee('target_guest@example.com');

        // Deactivate user
        $this->actingAs($admin)->post('/admin/users', [
            'user_id' => $guest->id,
            'action' => 'deactivate',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', ['id' => $guest->id, 'status' => 'deactivated']);

        // Reactivate user
        $this->actingAs($admin)->post('/admin/users', [
            'user_id' => $guest->id,
            'action' => 'activate',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', ['id' => $guest->id, 'status' => 'active']);
    }

    // --- FR-15: Records & Reports ---

    public function test_TC_F15a_to_e_admin_records_and_export(): void
    {
        $admin = $this->createTestUser('admin', 'admin_rec@example.com', 'Admin@123');
        $guest = $this->createTestUser('guest', 'guest_rec@example.com', 'Pass@1234');
        $cottageId = $this->createCottageRecord();
        $this->createBookingRecord($guest->id, $cottageId, now()->subDays(2)->toDateString(), now()->subDays(1)->toDateString(), 'completed');

        // View records
        $response = $this->actingAs($admin)->get('/admin/records?status=all');
        $response->assertOk()->assertSee('guest_rec@example.com');

        // Export CSV
        $csvResponse = $this->actingAs($admin)->get('/admin/records?export=csv');
        $csvResponse->assertOk();
        $this->assertStringContainsString('text/csv', (string) $csvResponse->headers->get('content-type'));
    }

    // --- FR-15* (TC-F18a/b): Cottage Owner Approval ---

    public function test_TC_F18a_b_admin_owner_approval_workflow(): void
    {
        $admin = $this->createTestUser('admin', 'admin_own@example.com', 'Admin@123');
        $owner = $this->createTestUser('owner', 'pending_owner@example.com', 'Pass@1234', 'pending');

        $this->actingAs($admin)->get('/admin/owners')->assertOk()->assertSee('pending_owner@example.com');

        $this->actingAs($admin)->post('/admin/owners/action', [
            'owner_id' => $owner->id,
            'action' => 'approve',
        ])->assertRedirect('/admin/owners');

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'status' => 'active']);
    }

    // --- NFR-03: Security & Access Control ---

    public function test_TC_N03c_d_unauthorized_access_redirection(): void
    {
        // Unauthenticated access to protected route redirects to login with safe next parameter
        $this->get('/profile')->assertRedirect('/login?next=%2Fprofile');
        $this->get('/booking')->assertRedirect('/login?next=%2Fbooking');
        $this->get('/admin')->assertRedirect('/login?next=%2Fadmin');

        // Guest accessing admin area gets 403 Forbidden
        $guest = $this->createTestUser('guest', 'regular_guest@example.com', 'Pass@1234');
        $this->actingAs($guest)->get('/admin')->assertForbidden();
    }

    // Helper methods
    private function createTestUser(string $role, string $email, string $password, string $status = 'active'): User
    {
        return User::create([
            'first_name' => ucfirst($role),
            'last_name' => 'Tester',
            'email' => $email,
            'phone' => '09170000000',
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'status' => $status,
        ]);
    }

    private function createCottageRecord(string $name = 'Test Cottage', int $capacity = 4, float $rate = 1000): int
    {
        return (int) DB::table('cottages')->insertGetId([
            'code' => 'CTG-' . random_int(1000, 9999),
            'name' => $name,
            'capacity' => $capacity,
            'rate_per_day' => $rate,
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    private function createRoomRecord(string $name = 'Test Room', int $capacity = 2, float $rate = 800): int
    {
        return (int) DB::table('rooms')->insertGetId([
            'code' => 'RM-' . random_int(1000, 9999),
            'name' => $name,
            'capacity' => $capacity,
            'rate_per_day' => $rate,
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    private function createBookingRecord(int $userId, int $cottageId, string $checkIn, string $checkOut, string $status): int
    {
        return (int) DB::table('bookings')->insertGetId([
            'receipt_no' => (string) random_int(100000, 999999),
            'user_id' => $userId,
            'cottage_id' => $cottageId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
            'adults' => 2,
            'children' => 0,
            'entrance_fee' => 200,
            'total_amount' => 1200,
            'payment_method' => 'pay_at_resort',
            'status' => $status,
            'created_at' => now(),
        ]);
    }
}
