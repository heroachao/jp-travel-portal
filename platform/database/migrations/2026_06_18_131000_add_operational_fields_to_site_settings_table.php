<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'tagline')) {
                $table->string('tagline')->nullable();
            }

            if (! Schema::hasColumn('site_settings', 'organization_schema_enabled')) {
                $table->boolean('organization_schema_enabled')->default(false);
            }

            if (! Schema::hasColumn('site_settings', 'contact_email')) {
                $table->string('contact_email')->nullable();
            }

            if (! Schema::hasColumn('site_settings', 'social_links')) {
                $table->json('social_links')->nullable();
            }

            if (! Schema::hasColumn('site_settings', 'robots_extra_rules')) {
                $table->text('robots_extra_rules')->nullable();
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            'tagline',
            'organization_schema_enabled',
            'contact_email',
            'social_links',
            'robots_extra_rules',
        ], fn (string $column): bool => Schema::hasColumn('site_settings', $column)));

        if ($columns === []) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
