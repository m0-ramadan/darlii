<?php

/**
 * Temporary smoke-test script for the CMS admin panel.
 * Usage: docker exec homzen_app php smoke_admin.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot the framework (facades, config, DB) with a throwaway request.
$kernel->handle(Request::create('/up', 'GET'));

$user = User::query()->where('email', 'admin@darlii.eg')->firstOrFail();
$property = Property::query()->first();
$manager = User::query()->where('email', 'manager@darlii.eg')->first();
$role = Role::query()->where('slug', 'agent')->first();

$routes = [
    'admin.dashboard' => [],
    'admin.stats.index' => [],
    'admin.stats.properties' => [],
    'admin.stats.users' => [],
    'admin.stats.export' => [],
    'admin.properties.index' => [],
    'admin.properties.create' => [],
    'admin.properties.pending' => [],
    'admin.properties.featured' => [],
    'admin.properties.assignments' => [],
    'admin.properties.show' => [$property],
    'admin.properties.edit' => [$property],
    'admin.properties.stats' => [$property],
    'admin.projects.index' => [],
    'admin.projects.create' => [],
    'admin.users.index' => [],
    'admin.users.create' => [],
    'admin.users.hierarchy' => [],
    'admin.users.show' => [$manager],
    'admin.users.edit' => [$manager],
    'admin.roles.index' => [],
    'admin.roles.show' => [$role],
    'admin.pages.index' => [],
    'admin.pages.create' => [],
    'admin.posts.index' => [],
    'admin.posts.create' => [],
    'admin.testimonials.index' => [],
    'admin.testimonials.create' => [],
    'admin.locations.index' => [],
    'admin.categories.index' => [],
    'admin.amenities.index' => [],
    'admin.media.index' => [],
    'admin.leads.index' => [],
    'admin.appointments.index' => [],
    'admin.reviews.index' => [],
    'admin.settings.general' => [],
    'admin.settings.branding' => [],
    'admin.settings.contact' => [],
    'admin.settings.seo' => [],
    'admin.settings.mail' => [],
    'admin.settings.integrations' => [],
    'admin.settings.storage' => [],
    'admin.settings.properties' => [],
    'admin.settings.panel' => [],
    'admin.audit.index' => [],
    'admin.profile.edit' => [],
];

$failures = [];
$ok = 0;

foreach ($routes as $name => $params) {
    try {
        $url = route($name, $params, false);
    } catch (\Throwable $e) {
        $failures[] = [$name, 'ROUTE', $e->getMessage()];
        continue;
    }

    $request = Request::create($url, 'GET');
    $request->setLaravelSession(app('session.store'));
    $app['auth']->setUser($user);
    app('session.store')->start();

    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $content = (string) $response->getContent();

        if ($status >= 400) {
            $snippet = '';
            if (preg_match('/<title>(.*?)<\/title>/s', $content, $m)) {
                $snippet = trim(strip_tags($m[1]));
            }
            if ($snippet === '' && preg_match('/(Exception|Error)[^<]{0,160}/', $content, $m)) {
                $snippet = trim($m[0]);
            }
            $failures[] = [$name, 'HTTP ' . $status, mb_substr($snippet, 0, 170)];
        } else {
            $ok++;
        }
    } catch (\Throwable $e) {
        $failures[] = [$name, 'EXCEPTION', get_class($e) . ': ' . mb_substr($e->getMessage(), 0, 170)];
    }
}

echo "\n===== SMOKE TEST: {$ok} OK / " . count($failures) . " FAILED =====\n";
foreach ($failures as [$name, $kind, $message]) {
    echo sprintf("%-42s %-12s %s\n", $name, $kind, $message);
}
echo "\n";
