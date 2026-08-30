<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_profiles', function (Blueprint $table): void {
            $table->string('avatar_path')->nullable()->after('bio');
        });

        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->string('image_path')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('status')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonial_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('testimonial_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('position')->nullable();
            $table->text('content');
            $table->unique(['testimonial_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial_translations');
        Schema::dropIfExists('testimonials');

        Schema::table('agent_profiles', function (Blueprint $table): void {
            $table->dropColumn('avatar_path');
        });
    }
};
