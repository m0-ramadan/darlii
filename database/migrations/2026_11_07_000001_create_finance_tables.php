<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_point_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('category')->nullable();
            $table->string('unit_label')->default('نقطة');
            $table->unsignedInteger('default_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('finance_point_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('point_type_id')->constrained('finance_point_types')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points');
            $table->string('frequency', 20)->default('monthly');
            $table->unsignedSmallInteger('interval_count')->default(1);
            $table->string('target_type', 20)->default('all');
            $table->string('target_role')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->timestamp('last_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('finance_point_rule_user', function (Blueprint $table): void {
            $table->foreignId('rule_id')->constrained('finance_point_rules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['rule_id', 'user_id']);
        });

        Schema::create('finance_point_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('point_type_id')->constrained('finance_point_types')->cascadeOnDelete();
            $table->bigInteger('balance')->default(0);
            $table->bigInteger('total_earned')->default(0);
            $table->bigInteger('total_spent')->default(0);
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'point_type_id']);
        });

        Schema::create('finance_point_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('point_type_id')->constrained('finance_point_types')->cascadeOnDelete();
            $table->bigInteger('points');
            $table->bigInteger('balance_after')->default(0);
            $table->string('source', 30)->default('manual');
            $table->string('description')->nullable();
            $table->foreignId('rule_id')->nullable()->constrained('finance_point_rules')->nullOnDelete();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'occurred_at']);
            $table->index(['point_type_id', 'occurred_at']);
        });

        Schema::create('finance_deposits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('EGP');
            $table->string('method', 30)->default('cash');
            $table->string('reference')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('finance_wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('cash_balance', 14, 2)->default(0);
            $table->decimal('total_deposited', 14, 2)->default(0);
            $table->bigInteger('points_balance')->default(0);
            $table->bigInteger('total_points_earned')->default(0);
            $table->bigInteger('total_points_spent')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_wallets');
        Schema::dropIfExists('finance_deposits');
        Schema::dropIfExists('finance_point_transactions');
        Schema::dropIfExists('finance_point_balances');
        Schema::dropIfExists('finance_point_rule_user');
        Schema::dropIfExists('finance_point_rules');
        Schema::dropIfExists('finance_point_types');
    }
};
