<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('active')->after('password');
            $table->string('timezone')->default('UTC')->after('status');
            $table->timestamp('next_checkin_due_at')->nullable()->after('timezone');
            $table->unsignedTinyInteger('daily_retry_day')->nullable()->after('next_checkin_due_at');
            $table->timestamp('disappeared_at')->nullable()->after('daily_retry_day');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'timezone', 'next_checkin_due_at', 'daily_retry_day', 'disappeared_at']);
        });
    }
};
