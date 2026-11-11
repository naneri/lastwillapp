<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan')->default('free');
            $table->string('pending_plan')->nullable();
            $table->timestamp('plan_change_effective_at')->nullable();
            $table->timestamp('free_tier_warning_sent_at')->nullable();
            $table->string('paddle_customer_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'plan',
                'pending_plan',
                'plan_change_effective_at',
                'free_tier_warning_sent_at',
                'paddle_customer_id',
            ]);
        });
    }
};
