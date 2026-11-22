<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_resets', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('verified_at')->nullable();
        });
        DB::table('password_resets')->where('used', false)->update(['used' => true]);

        Schema::create('account_deletion_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->timestamp('attempted_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'attempted_at'], 'idx_account_deletion_attempt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletion_attempts');

        Schema::table('password_resets', function (Blueprint $table) {
            $table->dropColumn(['attempts', 'verified_at']);
        });
    }
};
