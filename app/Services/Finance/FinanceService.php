<?php

namespace App\Services\Finance;

use App\Models\Finance\FinanceDeposit;
use App\Models\Finance\FinancePointBalance;
use App\Models\Finance\FinancePointRule;
use App\Models\Finance\FinancePointTransaction;
use App\Models\Finance\FinancePointType;
use App\Models\Finance\FinanceWallet;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceService
{
    public function walletFor(User $user): FinanceWallet
    {
        return FinanceWallet::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['cash_balance' => 0, 'total_deposited' => 0, 'points_balance' => 0, 'total_points_earned' => 0, 'total_points_spent' => 0]
        );
    }

    /**
     * إضافة/خصم نقاط مع تحديث الرصيد والدفتر والمحفظة في معاملة واحدة.
     *
     * @param  array{source?:string,description?:?string,rule_id?:?int,created_by?:?int,occurred_at?:?Carbon,metadata?:array,reference_type?:?string,reference_id?:?int}  $options
     */
    public function applyPoints(User $user, FinancePointType $type, int $points, array $options = []): FinancePointTransaction
    {
        if ($points === 0) {
            throw ValidationException::withMessages(['points' => 'عدد النقاط يجب ألا يكون صفرًا.']);
        }

        return DB::transaction(function () use ($user, $type, $points, $options): FinancePointTransaction {
            $wallet = $this->walletFor($user);
            $wallet = FinanceWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $balance = FinancePointBalance::query()->firstOrCreate(
                ['user_id' => $user->id, 'point_type_id' => $type->id],
                ['balance' => 0, 'total_earned' => 0, 'total_spent' => 0]
            );
            $balance = FinancePointBalance::query()->whereKey($balance->id)->lockForUpdate()->firstOrFail();

            $newBalance = (int) $balance->balance + $points;
            if ($newBalance < 0) {
                throw ValidationException::withMessages([
                    'points' => 'رصيد المستخدم من هذا النوع لا يكفي للخصم. الرصيد الحالي: ' . number_format((int) $balance->balance),
                ]);
            }

            $balance->balance = $newBalance;
            if ($points > 0) {
                $balance->total_earned = (int) $balance->total_earned + $points;
            } else {
                $balance->total_spent = (int) $balance->total_spent + abs($points);
            }
            $balance->last_transaction_at = now();
            $balance->save();

            $wallet->points_balance = (int) $wallet->points_balance + $points;
            if ($points > 0) {
                $wallet->total_points_earned = (int) $wallet->total_points_earned + $points;
            } else {
                $wallet->total_points_spent = (int) $wallet->total_points_spent + abs($points);
            }
            $wallet->save();

            return FinancePointTransaction::query()->create([
                'user_id' => $user->id,
                'point_type_id' => $type->id,
                'points' => $points,
                'balance_after' => $newBalance,
                'source' => $options['source'] ?? 'manual',
                'description' => $options['description'] ?? null,
                'rule_id' => $options['rule_id'] ?? null,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'created_by' => $options['created_by'] ?? auth()->id(),
                'occurred_at' => $options['occurred_at'] ?? now(),
                'metadata' => $options['metadata'] ?? null,
            ]);
        });
    }

    public function approveDeposit(FinanceDeposit $deposit, User $approver, ?string $adminNotes = null): FinanceDeposit
    {
        return DB::transaction(function () use ($deposit, $approver, $adminNotes): FinanceDeposit {
            $deposit = FinanceDeposit::query()->whereKey($deposit->id)->lockForUpdate()->firstOrFail();

            if ($deposit->status !== 'pending') {
                throw ValidationException::withMessages(['deposit' => 'لا يمكن اعتماد وديعة تمت مراجعتها بالفعل.']);
            }

            if ($deposit->user_id) {
                $wallet = $this->walletFor($deposit->user);
                $wallet = FinanceWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
                $wallet->cash_balance = (float) $wallet->cash_balance + (float) $deposit->amount;
                $wallet->total_deposited = (float) $wallet->total_deposited + (float) $deposit->amount;
                $wallet->save();
            }

            $deposit->status = 'approved';
            $deposit->approved_by = $approver->id;
            $deposit->approved_at = now();
            if ($adminNotes !== null) {
                $deposit->admin_notes = $adminNotes;
            }
            $deposit->save();

            return $deposit;
        });
    }

    public function rejectDeposit(FinanceDeposit $deposit, User $approver, ?string $adminNotes = null): FinanceDeposit
    {
        return DB::transaction(function () use ($deposit, $approver, $adminNotes): FinanceDeposit {
            $deposit = FinanceDeposit::query()->whereKey($deposit->id)->lockForUpdate()->firstOrFail();

            if ($deposit->status !== 'pending') {
                throw ValidationException::withMessages(['deposit' => 'لا يمكن رفض وديعة تمت مراجعتها بالفعل.']);
            }

            $deposit->status = 'rejected';
            $deposit->approved_by = $approver->id;
            $deposit->approved_at = now();
            if ($adminNotes !== null) {
                $deposit->admin_notes = $adminNotes;
            }
            $deposit->save();

            return $deposit;
        });
    }

    /** @return Collection<int, User> */
    public function targetUsers(FinancePointRule $rule): Collection
    {
        $query = User::query()->where('status', 'active');

        if ($rule->target_type === 'role' && $rule->target_role) {
            $query->whereHas('roles', fn ($q) => $q->where('slug', $rule->target_role));
        } elseif ($rule->target_type === 'users') {
            $ids = $rule->users()->pluck('users.id');
            $query->whereIn('id', $ids);
        } else {
            $query->whereDoesntHave('roles', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin']));
        }

        return $query->get();
    }

    /** تشغيل قاعدة نقاط دورية مرة واحدة (يدويًا أو من المجدول). */
    public function runRule(FinancePointRule $rule, bool $force = false): int
    {
        if (! $rule->is_active) {
            return 0;
        }

        if (! $force && $rule->next_run_at && $rule->next_run_at->isFuture()) {
            return 0;
        }

        $lock = Cache::lock('finance-point-rule:' . $rule->id, 180);
        if (! $lock->get()) {
            return 0;
        }

        try {
            $count = 0;
            foreach ($this->targetUsers($rule) as $user) {
                try {
                    $this->applyPoints($user, $rule->pointType, (int) $rule->points, [
                        'source' => 'recurring',
                        'description' => $rule->name,
                        'rule_id' => $rule->id,
                        'created_by' => auth()->id(),
                        'occurred_at' => now(),
                        'metadata' => ['frequency' => $rule->frequency, 'interval_count' => $rule->interval_count],
                    ]);
                    $count++;
                } catch (ValidationException $e) {
                    // رصيد سالب/نوع محذوف: نتجاوز المستخدم ولا نوقف بقية القاعدة.
                }
            }

            $rule->last_run_at = now();
            $rule->next_run_at = $this->nextOccurrence($rule, now());
            $rule->save();

            return $count;
        } finally {
            $lock->release();
        }
    }

    public function nextOccurrence(FinancePointRule $rule, Carbon $from): Carbon
    {
        $interval = max(1, (int) $rule->interval_count);
        $base = $rule->next_run_at && $rule->next_run_at->isFuture() ? $rule->next_run_at->copy() : $from->copy();

        return match ($rule->frequency) {
            'daily' => $base->addDays($interval),
            'weekly' => $base->addWeeks($interval),
            'yearly' => $base->addYears($interval),
            default => $base->addMonths($interval),
        };
    }
}
