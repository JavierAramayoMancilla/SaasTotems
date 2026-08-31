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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();

            // FK → tenants.id
            $table->foreignId('tenant_id')
                  ->constrained('tenants')
                  ->cascadeOnDelete();

            $table->string('code', 30);
            
            $table->unique(['tenant_id', 'code']);

            $table->string('name', 150);

            $table->string('type', 30);

            $table->string('media_path', 500)
                  ->nullable();

            $table->integer('duration')
                  ->default(10);

            $table->boolean('is_active')
                  ->default(true);

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
        Schema::dropIfExists('advertisements');
    }
};