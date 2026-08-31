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
        Schema::create('display_advertisements', function (Blueprint $table) {
            $table->id();

            // FK → displays.id
            $table->foreignId('display_id')
                  ->constrained('displays')
                  ->cascadeOnDelete();

            // FK → advertisements.id
            $table->foreignId('advertisement_id')
                  ->constrained('advertisements')
                  ->cascadeOnDelete();

            $table->integer('position')
                  ->default(1);

            $table->string('transition', 50)
                  ->default('fade');

            $table->boolean('is_active')
                  ->default(true);

            $table->timestamps();

            // Evita asociar la misma publicidad
            // dos veces al mismo display
            $table->unique([
                'display_id',
                'advertisement_id'
            ]);

            // Facilita ordenar las publicidades
            // dentro de un display
            $table->index([
                'display_id',
                'position'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_advertisements');
    }
};