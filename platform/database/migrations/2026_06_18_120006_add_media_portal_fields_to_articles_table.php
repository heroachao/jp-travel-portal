<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('source_name')->nullable()->after('body');
            $table->string('source_url')->nullable()->after('source_name');
            $table->timestamp('display_updated_at')->nullable()->after('published_at');
            $table->unsignedSmallInteger('reading_time_minutes')->nullable()->after('display_updated_at');
            $table->unsignedInteger('popularity_score')->default(0)->index()->after('reading_time_minutes');
            $table->boolean('has_coupon')->default(false)->index()->after('popularity_score');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn([
                'source_name',
                'source_url',
                'display_updated_at',
                'reading_time_minutes',
                'popularity_score',
                'has_coupon',
            ]);
        });
    }
};
