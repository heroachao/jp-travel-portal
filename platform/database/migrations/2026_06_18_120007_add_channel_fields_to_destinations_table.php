<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->string('display_name')->nullable()->after('name');
            $table->boolean('is_channel')->default(false)->index()->after('is_indexable');
            $table->unsignedInteger('sort_order')->default(0)->index()->after('is_channel');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->dropColumn(['display_name', 'is_channel', 'sort_order']);
        });
    }
};
