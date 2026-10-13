<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'price_type')) {
                $table->string('price_type', 20)->default('fixed')->after('price');
            }
            if (! Schema::hasColumn('properties', 'price_notes')) {
                $table->text('price_notes')->nullable()->after('price_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'price_notes')) {
                $table->dropColumn('price_notes');
            }
            if (Schema::hasColumn('properties', 'price_type')) {
                $table->dropColumn('price_type');
            }
        });
    }
};

