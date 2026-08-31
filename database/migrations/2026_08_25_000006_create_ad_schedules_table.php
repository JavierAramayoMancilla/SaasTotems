<?php

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
        Schema::create('ad_schedules', function (Blueprint $table) {
            $table->id();

            // FK → display_advertisements.id
            $table->foreignId('display_advertisement_id')
                  ->constrained('display_advertisements')
                  ->cascadeOnDelete();

            $table->integer('day_of_week');

            $table->time('start_time');

            $table->time('end_time');

            $table->timestamp('starts_at')
                  ->nullable();

            $table->timestamp('ends_at')
                  ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_schedules');
    }
};