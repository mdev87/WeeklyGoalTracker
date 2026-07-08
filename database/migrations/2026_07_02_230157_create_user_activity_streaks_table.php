<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_activity_streaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('current_streak')->default(1);
            $table->unsignedSmallInteger('longest_streak')->default(1);
            $table->date('last_active');

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_streaks');
    }
};
