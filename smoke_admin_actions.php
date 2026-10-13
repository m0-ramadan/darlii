<?php

/**
 * Temporary function test for the CMS: POST/PUT flows + per-role scoping.
 * Usage: docker exec homzen_app php smoke_admin_actions.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\Property;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Request::create('/up', 'GET'));

/** Fire a request as the given user and return [status, redirectLocation, contentSnippet]. */
function fire(string $method, string $url, User $user, array $data = []): array
{
    global $kernel;

    $session = app('session')->driver();
    $session->setId('smoke-' . $user->id . '-' . substr(md5($url . $method . microtime(true)), 0, 8));
    $session->start();

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $data['_token'] = $session->token();
    }

    $request = Request::create($url, $method, $data);
    $request->setLaravelSession($session);
    $request->setUserResolver(fn () => $user);
    app('auth')->setUser($user);

    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $body = (string) $response->getContent();
        $snippet = '';
        if ($status >= 400) {
            if (preg_match('/(?:Exception|Error)[^<]{0,200}/', $body, $m)) {
                $snippet = trim($m[0]);
            } elseif (preg_match('/<title>(.*?)<\/title>/s', $body, $m)) {
                $snippet = trim(strip_tags($m[1]));
            }
        }

        return [$status, $response->headers->get('Location'), $snippet];
    } catch (\Throwable $e) {
        $trace = collect($e->getTrace())->first(fn ($t) => isset($t['file']) && str_contains($t['file'], '/app/'));
        return [0, null, get_class($e) . ': ' . mb_substr($e->getMessage(), 0, 300)
            . ' @ ' . ($trace['file'] ?? '?') . ':' . ($trace['line'] ?? '?')];
    }
}

$results = [];
$admin = User::query()->where('email', 'admin@darlii.eg')->firstOrFail();
$manager = User::query()->where('email', 'manager@darlii.eg')->firstOrFail();
$agent = User::query()->where('email', 'agent1@darlii.eg')->firstOrFail();
$agentRole = Role::query()->where('slug', 'agent')->first();
$property = Property::query()->first();
$propertyCountBefore = Property::query()->count();

// ---------------------------------------------------------------- 1) إنشاء عقار
[$status, $loc, $err] = fire('POST', '/admin/properties', $admin, [
    'purpose' => 'sale',
    'status' => 'draft',
    'price' => '2500000',
    'currency_id' => (string) \App\Models\Currency::query()->where('is_default', true)->value('id'),
    'translations' => ['ar' => ['name' => 'شقة اختبار السي إم إس', 'slug' => 'cms-test-apartment', 'description' => 'وصف تجريبي']],
    'owner_id' => (string) $agent->id,
    'managed_by' => (string) $manager->id,
]);
$results['POST admin/properties (store)'] = [$status, $err];
$created = Property::query()->whereHas('translations', fn ($q) => $q->where('slug', 'cms-test-apartment'))->first();

// ---------------------------------------------------------------- 2) تعديل العقار
if ($created) {
    [$status, $loc, $err] = fire('PUT', '/admin/properties/' . $created->id, $admin, [
        'purpose' => 'rent',
        'status' => 'published',
        'price' => '99000',
        'currency_id' => (string) $created->currency_id,
        'translations' => ['ar' => ['name' => 'شقة اختبار معدّلة', 'slug' => 'cms-test-apartment', 'description' => 'محدّث']],
    ]);
    $results['PUT admin/properties/{id} (update)'] = [$status, $err];
    $results['  ↳ price/status persisted'] = [$created->fresh()->price == 99000 && $created->fresh()->status?->value === 'published' ? 200 : 500, null];
}

// ---------------------------------------------------------------- 3) اعتماد عقار
[$status, $loc, $err] = fire('POST', '/admin/properties/' . $property->id . '/approve', $admin, []);
$results['POST properties/{id}/approve'] = [$status, $err];

// ---------------------------------------------------------------- 4) إسناد عقار
[$status, $loc, $err] = fire('POST', '/admin/properties/' . $property->id . '/assign', $admin, [
    'owner_id' => (string) $agent->id,
    'managed_by' => (string) $manager->id,
]);
$results['POST properties/{id}/assign'] = [$status, $err];
$results['  ↳ assignment persisted'] = [$property->fresh()->managed_by === $manager->id ? 200 : 500, 'managed_by=' . var_export($property->fresh()->managed_by, true)];

// ---------------------------------------------------------------- 5) صلاحيات: الأدمن
[$status, $loc, $err] = fire('POST', '/admin/users', $admin, [
    'name' => 'سمسار تجريبي',
    'agent_profile' => ['job_title' => 'سمسار معتمد', 'whatsapp' => '+201111111111', 'years_experience' => '5'],
    'email' => 'cms-test-agent@darlii.eg',
    'status' => 'active',
    'roles' => [(string) $agentRole->id],
    'password' => 'Agent@12345',
    'password_confirmation' => 'Agent@12345',
    'manager_id' => (string) $manager->id,
]);
$results['POST admin/users (admin creates agent)'] = [$status, $err];
$testAgent = User::query()->where('email', 'cms-test-agent@darlii.eg')->first();
$results['  ↳ hierarchy persisted'] = [$testAgent && $testAgent->manager_id === $manager->id ? 200 : 500, 'manager_id=' . var_export($testAgent?->manager_id, true)];
$results['  ↳ agent profile created'] = [$testAgent && $testAgent->agentProfile ? 200 : 500, 'slug=' . var_export($testAgent?->agentProfile?->slug, true)];

// ---------------------------------------------------------------- 6) المسؤول ينشئ سمساراً
[$status, $loc, $err] = fire('POST', '/admin/users', $manager, [
    'name' => 'سمسار من المسؤول',
    'email' => 'cms-manager-agent@darlii.eg',
    'status' => 'active',
    'roles' => [(string) $agentRole->id],
    'password' => 'Agent@12345',
    'password_confirmation' => 'Agent@12345',
]);
$results['POST admin/users (manager creates agent)'] = [$status, $err];
$managerAgent = User::query()->where('email', 'cms-manager-agent@darlii.eg')->first();
$results['  ↳ forced under manager'] = [$managerAgent && $managerAgent->manager_id === $manager->id ? 200 : 500, $managerAgent ? ('manager_id=' . var_export($managerAgent->manager_id, true) . ' expected=' . $manager->id) : 'NO USER CREATED'];

// ---------------------------------------------------------------- 7) المسؤول لا ينشئ أدمن
$adminRole = Role::query()->where('slug', 'admin')->first();
[$status, $loc, $err] = fire('POST', '/admin/users', $manager, [
    'name' => 'محاولة أدمن',
    'email' => 'cms-rogue-admin@darlii.eg',
    'status' => 'active',
    'roles' => [(string) $adminRole->id],
    'password' => 'Agent@12345',
    'password_confirmation' => 'Agent@12345',
]);
$rogue = User::query()->where('email', 'cms-rogue-admin@darlii.eg')->first();
$results['POST admin/users (manager trying admin role)'] = [
    $rogue && ! $rogue->hasRole('admin') ? 200 : 500,
    $rogue ? 'created but role stripped (correct)' : 'no user created (correct)',
];

// ---------------------------------------------------------------- 8) السمسار لا يعدّل عقار غيره
// اختيار عقار مضمون أنه ليس ملك السمسار ولا يتابعه.
$foreign = Property::query()
    ->where('owner_id', '!=', $agent->id)
    ->where(fn ($q) => $q->whereNull('managed_by')->orWhere('managed_by', '!=', $agent->id))
    ->firstOrFail();
[$status, $loc, $err] = fire('PUT', '/admin/properties/' . $foreign->id, $agent, [
    'purpose' => 'sale', 'status' => 'published', 'price' => '1',
    'currency_id' => (string) $foreign->currency_id,
    'translations' => ['ar' => ['name' => 'محاولة تعديل غير مصرّح']],
]);
$results['PUT properties/{id} as unrelated agent (must be 403)'] = [$status === 403 ? 200 : 500, 'status=' . $status . ' ' . ($err ?? '')];

// ---------------------------------------------------------------- 9) الإعدادات
[$status, $loc, $err] = fire('PUT', '/admin/settings/general', $admin, [
    'general.site_name' => 'دارلي | Darlii',
    'general.currency_symbol' => 'ج.م',
    'general.currency_decimals' => '0',
    'general.items_per_page' => '15',
    'general.maintenance_mode' => '0',
]);
$results['PUT admin/settings/general'] = [$status, $err];

[$status, $loc, $err] = fire('PUT', '/admin/settings/does-not-exist', $admin, []);
$results['PUT admin/settings/{invalid} (must be 404)'] = [$status === 404 ? 200 : 500, 'status=' . $status];
[$status, $loc, $err] = fire('PUT', '/admin/settings/analytics', $admin, ['analytics_ga_enabled' => '1', 'analytics_ga_measurement_id' => 'G-TEST123']);
$results['PUT admin/settings/analytics'] = [$status, $err];
app(\App\Services\Settings\SettingsRepository::class)->flush();
$results['  ↳ GA id persisted'] = [\App\Models\Setting::query()->where('key', 'analytics.ga_measurement_id')->value('value') === 'G-TEST123' ? 200 : 500, 'db=' . var_export(\App\Models\Setting::query()->where('key', 'analytics.ga_measurement_id')->value('value'), true)];

// ---------------------------------------------------------------- 10) اختبار التخزين
[$status, $loc, $err] = fire('POST', '/admin/settings/test-connection', $admin, ['service' => 'storage']);
$results['POST settings/test-connection (storage)'] = [$status, $err];

// ---------------------------------------------------------------- 11) الأدوار
[$status, $loc, $err] = fire('POST', '/admin/roles/' . $agentRole->id . '/permissions', $admin, [
    'permissions' => $agentRole->permissions()->pluck('id')->map(fn ($id) => (string) $id)->all(),
]);
$results['POST roles/{id}/permissions'] = [$status, $err];

// ---------------------------------------------------------------- 12) نطاق العرض
[$adminScope, , ] = fire('GET', '/admin/properties', $admin);
[$agentScope, , ] = fire('GET', '/admin/properties', $agent);
$results['GET admin/properties as admin'] = [$adminScope, null];
$results['GET admin/properties as agent'] = [$agentScope, null];

// ---------------------------------------------------------------- 13) صفحات عامة
[$status, $loc, $err] = fire('GET', '/admin', $agent, []);
$results['GET /admin as agent (panel access)'] = [$status, $err];
$customer = User::query()->whereHas('roles', fn ($q) => $q->where('slug', 'customer'))->first();
if ($customer) {
    [$status, $loc, $err] = fire('GET', '/admin', $customer, []);
    $results['GET /admin as customer (must be 403)'] = [$status === 403 ? 200 : 500, 'status=' . $status];
}

// ---------------------------------------------------------------- 14) تنظيف
Property::query()->whereHas('translations', fn ($q) => $q->where('slug', 'cms-test-apartment'))->forceDelete();
User::query()->whereIn('email', ['cms-test-agent@darlii.eg', 'cms-manager-agent@darlii.eg', 'cms-rogue-admin@darlii.eg'])->forceDelete();

$fail = 0;
echo "\n===== ACTION TEST =====\n";
foreach ($results as $label => [$code, $note]) {
    $good = in_array($code, [200, 302], true);
    if (! $good) {
        $fail++;
    }
    echo sprintf("%-58s %s %s\n", $label, $good ? 'PASS' : 'FAIL(' . $code . ')', $note ?? '');
}
echo "\n" . ($fail === 0 ? "ALL PASSED\n" : "{$fail} FAILED\n");
