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
        Schema::create('displays', function (Blueprint $table) {
            $table->id();

            // Tenant propietario del display.
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            // Identificador único público del display.
            $table->uuid('uuid')
                ->unique();

            // Código interno/administrativo del display.
            $table->string('code', 30)
                ->unique();

            // Nombre visible del display.
            $table->string('name', 150);

            // Estado del display.
            $table->string('status', 30)
                ->default('active');

            // Última sincronización del display.
            $table->timestamp('last_sync_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('displays');
    }
};