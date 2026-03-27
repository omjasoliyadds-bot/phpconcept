<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp', 255)->nullable();
            $table->string('otp_token', 100)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->integer('otp_attempts')->default(0);
            $table->timestamp('otp_last_sent_at')->nullable();
            $table->boolean('is_first_login')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'otp',
                'otp_token',
                'otp_expires_at',
                'otp_attempts',
                'otp_last_sent_at',
                'is_first_login'
            ]);
        });
    }
};