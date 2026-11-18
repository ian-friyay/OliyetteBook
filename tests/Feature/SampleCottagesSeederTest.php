<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SampleCottagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SampleCottagesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_cottages_are_added_idempotently_without_seeding_accounts(): void
    {
        $this->seed(SampleCottagesSeeder::class);
        $this->seed(SampleCottagesSeeder::class);

        $this->assertSame(7, DB::table('cottages')->count());
        $this->assertSame(6, DB::table('cottages')->where('status', 'active')->count());
        $this->assertSame(8, DB::table('cottage_photos')->count());
        $this->assertSame(0, DB::table('users')->count());
    }

    public function test_admin_cottages_page_can_load_without_joined_review_grouping(): void
    {
        $admin = User::create([
            'first_name' => 'Site',
            'last_name' => 'Administrator',
            'email' => 'admin@oliyettebook.test',
            'phone' => '09170000001',
            'password_hash' => password_hash('Admin@123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->seed(SampleCottagesSeeder::class);

        $response = $this->actingAs($admin)->get('/admin/cottages');

        $response->assertOk();
        $response->assertSee('Cottage Management');
    }
}
