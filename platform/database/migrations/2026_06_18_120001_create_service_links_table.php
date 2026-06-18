<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_links', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 40)->index();
            $table->string('label');
            $table->string('url');
            $table->string('placement', 40)->default('header')->index();
            $table->string('tracking_key')->nullable()->index();
            $table->text('notes')->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_links');
    }
};
