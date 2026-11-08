<?php

namespace Database\Seeders;

use App\Models\Finance\FinancePointType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FinancePointTypeSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'مكافأة التسجيل', 'slug' => 'registration', 'category' => 'مكافآت', 'default_points' => 100, 'description' => 'نقاط تُمنح عند إنشاء حساب جديد.'],
            ['name' => 'إحالة صديق', 'slug' => 'referral', 'category' => 'إحالات', 'default_points' => 250, 'description' => 'نقاط تُمنح عند إحالة عميل جديد.'],
            ['name' => 'إتمام عملية', 'slug' => 'purchase', 'category' => 'مبيعات', 'default_points' => 500, 'description' => 'نقاط تُمنح بعد إتمام بيع أو إيجار.'],
            ['name' => 'ولاء دوري', 'slug' => 'loyalty', 'category' => 'ولاء', 'default_points' => 50, 'description' => 'نقاط دورية للعملاء النشطين.'],
            ['name' => 'مكافأة إدارية', 'slug' => 'bonus', 'category' => 'إدارية', 'default_points' => 0, 'description' => 'نقاط يمنحها السوبر أدمن يدويًا.'],
        ];

        foreach ($defaults as $i => $row) {
            FinancePointType::query()->firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'category' => $row['category'],
                    'unit_label' => 'نقطة',
                    'default_points' => $row['default_points'],
                    'is_active' => true,
                    'sort_order' => $i,
                ]
            );
        }
    }
}
