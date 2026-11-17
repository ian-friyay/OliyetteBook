<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cottages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 20)->unique();
            $table->unsignedInteger('owner_id')->nullable();
            $table->string('name', 100);
            $table->string('location', 100)->nullable();
            $table->text('description')->nullable();
            $table->integer('capacity')->default(4);
            $table->text('amenities')->nullable();
            $table->decimal('rate_per_day', 10, 2);
            $table->enum('status', ['pending', 'active', 'hidden'])->default('pending');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('owner_id')->references('id')->on('users');
        });

        Schema::create('cottage_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cottage_id');
            $table->string('path', 255);
            $table->integer('sort_order')->default(0);

            $table->foreign('cottage_id')->references('id')->on('cottages')->onDelete('cascade');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('receipt_no', 20)->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('cottage_id');
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->text('special_requests')->nullable();
            $table->decimal('entrance_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['gcash', 'pay_at_resort'])->default('pay_at_resort');
            $table->enum('status', ['pending', 'approved', 'declined', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('cottage_id')->references('id')->on('cottages');
            $table->index(['cottage_id', 'check_in', 'check_out'], 'idx_dates');
            $table->index('status', 'idx_booking_status');
            $table->index('created_at', 'idx_booking_created');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('booking_id')->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('cottage_id');
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'removed'])->default('pending');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('booking_id')->references('id')->on('bookings');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('cottage_id')->references('id')->on('cottages');
            $table->index('status', 'idx_review_status');
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('email', 120);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->char('token_hash', 64);
            $table->dateTime('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('token_hash', 'idx_reset_token');
        });

        Schema::create('login_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 120);
            $table->string('ip_address', 45);
            $table->timestamp('attempted_at')->useCurrent();

            $table->index(['email', 'ip_address', 'attempted_at'], 'idx_login_attempt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('cottage_photos');
        Schema::dropIfExists('cottages');
    }
};
