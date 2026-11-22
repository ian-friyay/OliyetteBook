<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')
            ->where('payment_method', 'gcash')
            ->update(['payment_method' => 'pay_at_resort']);
    }

    public function down(): void
    {
    }
};
