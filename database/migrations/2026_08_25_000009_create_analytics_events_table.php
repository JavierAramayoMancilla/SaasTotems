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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();

            // FK → tenants.id
            $table->foreignId('tenant_id')
                  ->constrained('tenants')
                  ->cascadeOnDelete();

            // FK → displays.id
            $table->foreignId('display_id')
                  ->constrained('displays')
                  ->cascadeOnDelete();

            $table->string('event_type', 50);

            // FK → advertisements.id
            $table->foreignId('advertisement_id')
                  ->nullable()
                  ->constrained('advertisements')
                  ->nullOnDelete();

            // FK → menu_items.id
            $table->foreignId('menu_item_id')
                  ->nullable()
                  ->constrained('menu_items')
                  ->nullOnDelete();

            $table->string('session_id', 100)
                  ->nullable();

            $table->timestamp('started_at');

            $table->integer('duration')
                  ->nullable();

            $table->json('metadata')
                  ->nullable();

            $table->timestamp('created_at')
                  ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};