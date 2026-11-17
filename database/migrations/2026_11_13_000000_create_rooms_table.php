<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('location', 100)->nullable();
            $table->text('description')->nullable();
            $table->integer('capacity')->default(2);
            $table->text('amenities')->nullable();
            $table->decimal('rate_per_day', 10, 2);
            $table->string('photo', 255)->nullable();
            $table->enum('status', ['pending', 'active', 'hidden'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('cottage_id')->nullable()->change();
            $table->unsignedInteger('room_id')->nullable()->after('cottage_id');
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->index(['room_id', 'check_in', 'check_out'], 'idx_room_dates');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropIndex('idx_room_dates');
            $table->dropColumn('room_id');
            $table->unsignedInteger('cottage_id')->nullable(false)->change();
        });

        Schema::dropIfExists('rooms');
    }
};
