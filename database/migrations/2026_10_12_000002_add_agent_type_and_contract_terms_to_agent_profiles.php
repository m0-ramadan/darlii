<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_profiles', function (Blueprint $table) {
            // نوع المتعامل: شخص طبيعي، مكتب عقارات، سمسار عادي
            $table->string('agent_type')->default('individual')->after('job_title');
            // بنود التعامل: نسب عمولة، شروط، ملاحظات خاصة - نص حر للمدير
            $table->text('contract_terms')->nullable()->after('agent_type');
        });
    }

    public function down(): void
    {
        Schema::table('agent_profiles', function (Blueprint $table) {
            $table->dropColumn(['agent_type', 'contract_terms']);
        });
    }
};

