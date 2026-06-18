<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name')->default('Japan Travel Guide');
            $table->string('seo_title_suffix')->default('Japan Travel Guide');
            $table->text('default_meta_description')->nullable();
            $table->string('ga4_measurement_id')->nullable();
            $table->string('adsense_publisher_id')->nullable();
            $table->boolean('analytics_enabled')->default(false);
            $table->boolean('ads_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
