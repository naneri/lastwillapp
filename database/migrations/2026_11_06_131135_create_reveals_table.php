<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reveals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('will_receiver_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('sent_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'will_receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reveals');
    }
};
