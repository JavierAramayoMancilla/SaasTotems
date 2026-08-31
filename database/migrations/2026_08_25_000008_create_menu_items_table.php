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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            // FK → menus.id
            $table->foreignId('menu_id')
                  ->constrained('menus')
                  ->cascadeOnDelete();

            // FK → menu_items.id
            // Permite crear elementos anidados
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('menu_items')
                  ->cascadeOnDelete();

            $table->string('title', 150);

            $table->string('type', 30);

            $table->text('content')
                  ->nullable();

            $table->string('media_path', 500)
                  ->nullable();

            $table->string('url', 1000)
                  ->nullable();

            $table->integer('position')
                  ->default(0);

            $table->boolean('is_active')
                  ->default(true);

            $table->timestamps();

            // Índice para organizar los elementos
            // dentro de un menú
            $table->index([
                'menu_id',
                'parent_id',
                'position'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};