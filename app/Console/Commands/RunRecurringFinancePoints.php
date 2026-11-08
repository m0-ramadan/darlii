<?php

namespace App\Console\Commands;

use App\Models\Finance\FinancePointRule;
use App\Services\Finance\FinanceService;
use Illuminate\Console\Command;

class RunRecurringFinancePoints extends Command
{
    protected $signature = 'finance:run-recurring-points {--force : تجاهل وقت الاستحقاق}';

    protected $description = 'توزيع النقاط الدورية المستحقة على المستخدمين';

    public function handle(FinanceService $service): int
    {
        if (! config('finance.points_enabled', false)) {
            $this->warn('بند النقاط متوقف حاليًا. لن يتم توزيع أي نقاط.');

            return self::SUCCESS;
        }

        $rules = FinancePointRule::query()
            ->when(! $this->option('force'), fn ($q) => $q->due())
            ->where('is_active', true)
            ->with('pointType')
            ->get();

        $total = 0;
        foreach ($rules as $rule) {
            if (! $rule->pointType) {
                continue;
            }
            $count = $service->runRule($rule, (bool) $this->option('force'));
            $total += $count;
            $this->line("Rule #{$rule->id} ({$rule->name}): {$count} user(s)");
        }

        $this->info("Done. Awarded {$total} point transaction(s).");

        return self::SUCCESS;
    }
}
