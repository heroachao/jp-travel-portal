<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_module_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('homepage_module_id')->constrained()->cascadeOnDelete();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->string('label')->nullable();
            $table->text('summary')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_module_items');
    }
};
