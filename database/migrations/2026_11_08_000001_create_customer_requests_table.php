<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('customer_name', 120);
            $table->string('phone', 30);
            $table->text('need');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('address_details')->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['location_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_requests');
    }
};
