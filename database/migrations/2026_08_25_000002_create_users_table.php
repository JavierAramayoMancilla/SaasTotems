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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Relación con tenants
            // NULL únicamente para el SuperAdmin
            $table->foreignId('tenant_id')
                    ->nullable()
                    ->constrained('tenants')
                    ->nullOnDelete();

            $table->string('code', 30)
                  ->unique();

            $table->string('name', 150);

            $table->string('email', 255)
                  ->unique();

            $table->timestamp('email_verified_at')
                  ->nullable();

            $table->string('password', 255);

            $table->rememberToken();

            $table->string('status', 30)
                  ->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};