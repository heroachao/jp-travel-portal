<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'google_site_verification')) {
                $table->string('google_site_verification')->nullable()->after('ga4_measurement_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('site_settings', 'google_site_verification')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn('google_site_verification');
        });
    }
};
