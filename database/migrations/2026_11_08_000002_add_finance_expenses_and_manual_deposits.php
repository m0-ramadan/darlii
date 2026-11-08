<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_deposits', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('depositor_name', 120)->nullable()->after('user_id');
            $table->string('depositor_phone', 30)->nullable()->after('depositor_name');
        });

        Schema::create('finance_expenses', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('category', 50)->default('other')->index();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('EGP');
            $table->string('method', 30)->default('cash');
            $table->string('reference')->nullable();
            $table->date('expense_date')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_expenses');
        Schema::table('finance_deposits', function (Blueprint $table): void {
            $table->dropColumn(['depositor_name', 'depositor_phone']);
        });
    }
};
