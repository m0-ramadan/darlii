<?php

namespace Tests\Feature;

use App\Models\Finance\FinanceDeposit;
use App\Models\Finance\FinancePointRule;
use App\Models\Finance\FinancePointType;
use App\Models\Finance\FinancePointTransaction;
use App\Models\Finance\FinanceWallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['finance.points_enabled' => true]);
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function superAdmin(): User
    {
        return User::where('email', 'super@darlii.eg')->firstOrFail();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@darlii.eg')->firstOrFail();
    }

    private function agent(): User
    {
        return User::where('email', 'agent1@darlii.eg')->firstOrFail();
    }

    public function test_only_super_admin_can_access_finance(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/finance')->assertOk();
        $this->actingAs($this->superAdmin())->get('/admin/finance/stats')->assertOk();
        $this->actingAs($this->admin())->get('/admin/finance')->assertForbidden();
        $this->actingAs($this->agent())->get('/admin/finance')->assertForbidden();
    }

    public function test_super_admin_is_hidden_from_other_admin_users_pages(): void
    {
        $this->actingAs($this->admin())->get('/admin/users')->assertOk()->assertDontSee('super@darlii.eg');
        $this->actingAs($this->admin())->get('/admin/users/hierarchy')->assertOk()->assertDontSee('super@darlii.eg');
        $this->actingAs($this->superAdmin())->get('/admin/users')->assertOk()->assertSee('super@darlii.eg');
    }

    public function test_point_types_can_be_managed(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/finance/point-types', [
                'name' => 'نقاط اختبار', 'slug' => 'test-points', 'unit_label' => 'نقطة',
                'default_points' => 10, 'is_active' => 1, 'sort_order' => 5,
            ])->assertRedirect(route('admin.finance.point-types.index'));

        $type = FinancePointType::where('slug', 'test-points')->firstOrFail();
        $this->assertSame(10, $type->default_points);
    }

    public function test_manual_points_update_creates_ledger_and_wallet(): void
    {
        $type = FinancePointType::where('slug', 'bonus')->firstOrFail();
        $agent = $this->agent();

        $this->actingAs($this->superAdmin())->post('/admin/finance/point-transactions', [
            'user_id' => $agent->id,
            'point_type_id' => $type->id,
            'points' => 120,
            'description' => 'مكافأة اختبار',
        ])->assertRedirect(route('admin.finance.point-transactions.index'));

        $this->assertDatabaseHas('finance_point_transactions', ['user_id' => $agent->id, 'points' => 120, 'source' => 'manual']);
        $this->assertSame(120, (int) FinanceWallet::where('user_id', $agent->id)->value('points_balance'));
    }

    public function test_points_cannot_go_negative(): void
    {
        $type = FinancePointType::where('slug', 'bonus')->firstOrFail();

        $this->actingAs($this->superAdmin())->from('/admin/finance/point-transactions/create')
            ->post('/admin/finance/point-transactions', [
                'user_id' => $this->agent()->id,
                'point_type_id' => $type->id,
                'points' => -5,
                'description' => 'خصم غير مسموح',
            ])->assertSessionHasErrors('points');

        $this->assertSame(0, FinancePointTransaction::count());
    }

    public function test_deposit_approval_updates_cash_wallet(): void
    {
        $agent = $this->agent();
        $this->actingAs($this->superAdmin())->post('/admin/finance/deposits', [
            'user_id' => $agent->id,
            'amount' => 1500,
            'currency' => 'EGP',
            'method' => 'cash',
        ])->assertRedirect();

        $deposit = FinanceDeposit::firstOrFail();
        $this->actingAs($this->superAdmin())->post('/admin/finance/deposits/' . $deposit->id . '/approve')
            ->assertRedirect();

        $deposit->refresh();
        $wallet = FinanceWallet::where('user_id', $agent->id)->firstOrFail();
        $this->assertSame('approved', $deposit->status);
        $this->assertSame('1500.00', $wallet->cash_balance);
        $this->assertSame('1500.00', $wallet->total_deposited);
    }

    public function test_recurring_rule_awards_points_and_advances_schedule(): void
    {
        $type = FinancePointType::where('slug', 'loyalty')->firstOrFail();
        $rule = FinancePointRule::create([
            'point_type_id' => $type->id,
            'name' => 'نقاط شهرية',
            'points' => 25,
            'frequency' => 'monthly',
            'interval_count' => 1,
            'target_type' => 'all',
            'next_run_at' => now()->subMinute(),
            'is_active' => true,
            'created_by' => $this->superAdmin()->id,
        ]);

        $this->artisan('finance:run-recurring-points')->assertExitCode(0);

        $this->assertGreaterThan(0, FinancePointTransaction::where('rule_id', $rule->id)->count());
        $this->assertTrue($rule->fresh()->next_run_at->isFuture());
        $this->assertDatabaseMissing('finance_point_transactions', ['rule_id' => $rule->id, 'user_id' => $this->superAdmin()->id]);
    }

    public function test_finance_pages_render_for_super_admin(): void
    {
        $type = FinancePointType::firstOrFail();
        $rule = FinancePointRule::create([
            'point_type_id' => $type->id,
            'name' => 'قاعدة عرض',
            'points' => 10,
            'frequency' => 'monthly',
            'interval_count' => 1,
            'target_type' => 'all',
            'next_run_at' => now()->addDay(),
            'is_active' => true,
        ]);
        $deposit = FinanceDeposit::create([
            'user_id' => $this->agent()->id,
            'amount' => 100,
            'currency' => 'EGP',
            'method' => 'cash',
            'status' => 'pending',
        ]);

        foreach ([
            '/admin/finance', '/admin/finance/stats', '/admin/finance/point-types',
            '/admin/finance/point-types/create', '/admin/finance/point-types/' . $type->id . '/edit',
            '/admin/finance/point-rules', '/admin/finance/point-rules/create', '/admin/finance/point-rules/' . $rule->id . '/edit',
            '/admin/finance/point-transactions', '/admin/finance/point-transactions/create',
            '/admin/finance/deposits', '/admin/finance/deposits/create', '/admin/finance/deposits/' . $deposit->id,
            '/admin/finance/expenses', '/admin/finance/expenses/create',
            '/admin/finance/balances', '/admin/finance/balances/' . $this->agent()->id,
        ] as $url) {
            $this->actingAs($this->superAdmin())->get($url)->assertOk();
        }

        $this->actingAs($this->superAdmin())->get('/admin/finance/stats/export')
            ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_manual_deposit_without_registered_user_can_be_recorded_and_approved(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/finance/deposits', [
            'depositor_name' => 'مودع غير مسجل',
            'depositor_phone' => '01099998888',
            'amount' => 750,
            'currency' => 'EGP',
            'method' => 'cash',
        ])->assertRedirect();

        $deposit = FinanceDeposit::firstOrFail();
        $this->assertNull($deposit->user_id);
        $this->assertSame('مودع غير مسجل', $deposit->depositor_name);

        $this->actingAs($this->superAdmin())->post('/admin/finance/deposits/' . $deposit->id . '/approve')
            ->assertRedirect();

        $this->assertSame('approved', $deposit->fresh()->status);
        $this->assertSame(0, FinanceWallet::count());
    }

    public function test_expenses_can_be_recorded_and_appear_in_stats(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/finance/expenses', [
            'title' => 'إيجار المكتب',
            'category' => 'rent',
            'amount' => 5000,
            'currency' => 'EGP',
            'method' => 'bank_transfer',
            'expense_date' => now()->toDateString(),
        ])->assertRedirect(route('admin.finance.expenses.index'));

        $this->assertDatabaseHas('finance_expenses', ['title' => 'إيجار المكتب', 'amount' => 5000]);
        $this->actingAs($this->superAdmin())->get('/admin/finance/stats')->assertOk()->assertSee('إيجارات')->assertSee('المصروفات حسب التصنيف');
    }

    public function test_points_section_is_hidden_and_blocked_when_disabled(): void
    {
        config(['finance.points_enabled' => false]);

        $this->actingAs($this->superAdmin())->get('/admin/finance')->assertOk()->assertDontSee('أنواع النقاط');
        $this->actingAs($this->superAdmin())->get('/admin/finance/point-types')
            ->assertRedirect(route('admin.finance.index'));
        $this->actingAs($this->superAdmin())->get('/admin/finance/point-transactions')
            ->assertRedirect(route('admin.finance.index'));
    }

    public function test_arabic_point_type_gets_an_automatic_slug(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/finance/point-types', [
            'name' => 'نقاط عربية', 'unit_label' => 'نقطة', 'is_active' => 1,
        ])->assertRedirect(route('admin.finance.point-types.index'));

        $type = FinancePointType::where('name', 'نقاط عربية')->firstOrFail();
        $this->assertNotSame('', $type->slug);
        $this->assertSame(1, FinancePointType::where('slug', $type->slug)->count());
    }
}
