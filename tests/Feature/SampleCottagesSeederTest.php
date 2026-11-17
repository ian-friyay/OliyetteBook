<?php

namespace Tests\Feature;

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
}
