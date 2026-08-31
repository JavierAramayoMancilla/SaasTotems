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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();

            // FK → tenants.id
            $table->foreignId('tenant_id')
                  ->constrained('tenants')
                  ->cascadeOnDelete();

            // FK → advertisements.id
            $table->foreignId('advertisement_id')
                  ->constrained('advertisements')
                  ->cascadeOnDelete();

            $table->string('name', 150);

            $table->string('slug', 150);

            $table->boolean('is_active')
                  ->default(true);

            $table->integer('version')
                  ->default(1);

            $table->timestamp('published_at')
                  ->nullable();

            $table->timestamps();

            // Un mismo slug no puede repetirse dentro del mismo tenant.
            $table->unique(['tenant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};