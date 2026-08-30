<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('preferred_locale', 10)->nullable()->after('phone');
            $table->string('preferred_currency', 3)->nullable()->after('preferred_locale');
            $table->string('preferred_theme', 10)->nullable()->after('preferred_currency');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['preferred_locale', 'preferred_currency', 'preferred_theme']);
        });
    }
};
