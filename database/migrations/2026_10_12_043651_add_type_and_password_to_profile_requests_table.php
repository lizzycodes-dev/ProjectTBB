<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_requests', function (Blueprint $table) {
            // 'profile' | 'password'
            $table->string('type')->default('profile')->after('user_id');

            // Requested password (hashed) — only used when type = 'password'
            $table->string('password')->nullable()->after('email');

            // Make email nullable now that password requests don't need it
            $table->string('email')->nullable()->change();

            // Make name nullable too
            $table->string('name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('profile_requests', function (Blueprint $table) {
            $table->dropColumn(['type', 'password']);
        });
    }
};