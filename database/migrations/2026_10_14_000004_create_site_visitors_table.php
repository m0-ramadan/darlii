<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visitors', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id', 64)->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->index();

            // Geographical details
            $table->string('country_code', 5)->default('EG')->index();
            $table->string('country_name', 100)->default('مصر');
            $table->string('governorate', 100)->nullable()->index();
            $table->string('city_or_markaz', 100)->nullable()->index();
            $table->string('district', 150)->nullable();
            $table->string('formatted_address', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_source', 20)->default('ip');
            $table->unsignedInteger('accuracy_meters')->nullable();

            // Device & Browser details
            $table->string('device_type', 30)->default('desktop')->index();
            $table->string('device_brand', 60)->nullable();
            $table->string('device_model', 60)->nullable();
            $table->string('os', 60)->nullable();
            $table->string('browser', 60)->nullable();
            $table->string('browser_language', 20)->nullable();
            $table->string('screen_resolution', 30)->nullable();
            $table->text('user_agent')->nullable();

            // Navigation & Session context
            $table->string('landing_page', 500)->nullable();
            $table->string('last_page', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->unsignedInteger('visits_count')->default(1);
            $table->unsignedInteger('page_views_count')->default(1);
            $table->timestamp('first_visited_at')->nullable();
            $table->timestamp('last_active_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('site_visitor_hits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('site_visitors')->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('page_title', 255)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visitor_hits');
        Schema::dropIfExists('site_visitors');
    }
};

