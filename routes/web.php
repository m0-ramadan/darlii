<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\Dashboard\PropertyController as DashboardPropertyController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LegacyCompatibilityController;
use App\Http\Controllers\LegacyFrontendController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyMapController;
use App\Http\Controllers\ReviewController;
use App\Services\Preferences\VisitorPreferences;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request, VisitorPreferences $preferences) {
    return redirect('/' . $preferences->locale($request));
});

Route::get('/homepage-{variant}', function () {
    return redirect('/' . config('locales.default'), 301);
})->whereIn('variant', ['1', '2', '3', '4', '5']);

Route::get('/language/switch/{locale}', LocaleController::class)
    ->whereIn('locale', config('locales.supported'))
    ->name('locale.switch');


// Compatibility endpoints used by the untouched original Homzen frontend.
Route::post('/newsletter/subscribe', [LegacyCompatibilityController::class, 'newsletter'])->middleware('throttle:20,1');
Route::post('/send-consult', [LegacyCompatibilityController::class, 'consult'])->middleware('throttle:10,1');
Route::post('/contact/send', [LegacyCompatibilityController::class, 'contact'])->middleware('throttle:10,1');
Route::post('/ajax/review/{slug}', [LegacyCompatibilityController::class, 'review'])->middleware('throttle:5,1');
Route::match(['get', 'post'], '/ajax/render-ui-blocks', [LegacyCompatibilityController::class, 'renderUiBlocks'])->middleware('throttle:60,1');
Route::get('/currency/switch/{code}', [LegacyCompatibilityController::class, 'currency']);
Route::get('/account/properties', function () {
    if (! auth()->check()) {
        return redirect('/' . config('locales.default', 'ar') . '/login');
    }
    $user = auth()->user();
    if ($user->hasAnyRole(\App\Support\PermissionCatalog::panelRoles())) {
        return redirect('/admin/properties');
    }
    return redirect('/' . config('locales.default', 'ar') . '/dashboard/properties');
});
Route::get('/ajax/properties/map', [PropertyMapController::class, 'index'])->middleware('throttle:60,1');
Route::get('/ajax/cities', [PropertyMapController::class, 'cities'])->middleware('throttle:60,1');
Route::get('/ajax/projects/search', [LegacyCompatibilityController::class, 'projectSearch'])->middleware('throttle:60,1');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('global.logout');

Route::prefix('{locale}')
    ->whereIn('locale', ['ar', 'en'])
    ->middleware(['set.locale', 'set.currency'])
    ->group(function () {
        Route::get('/homepage-{variant}', function (string $locale) {
            return redirect('/' . $locale, 301);
        })->whereIn('variant', ['1', '2', '3', '4', '5']);

        // Public GET pages are rendered from the exact frontend files supplied by the user.
        Route::get('/', [LegacyFrontendController::class, 'home'])->name('home');
        // صفحات العقارات والمشروعات تُعرض من قاعدة البيانات (محتوى لوحة التحكم)
        // مع إعادة استخدام نفس الهيدر والفوتر الخاصين بتصميم هومزن الأصلي.
        Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
        Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
        Route::get('/agents/{slug}', [AgentController::class, 'show'])->name('agents.show');
        // المدونة والوظائف تُعرض من قاعدة البيانات (محتوى لوحة التحكم).
        Route::get('/blog', [ContentController::class, 'blog'])->name('blog.index');
        Route::get('/blog/{slug}', [ContentController::class, 'post'])->name('blog.show');
        Route::get('/news/{slug}', fn(Request $request, string $locale, string $slug) => app(LegacyFrontendController::class)->page($request, $locale, 'news/' . $slug))->name('news.show');
        Route::get('/careers', [ContentController::class, 'careers'])->name('careers.index');
        Route::get('/careers/{slug}', [ContentController::class, 'career'])->name('careers.show');
        Route::get('/faqs', [ContentController::class, 'faqs'])->name('faqs.index');
        Route::get('/privacy-policy', [ContentController::class, 'privacyPolicy'])->name('privacy-policy.index');
        Route::get('/contact-us', [ContentController::class, 'contactUs'])->name('contact-us.index');
        Route::post('/contact-us', [ContentController::class, 'submitContact'])->name('contact-us.submit');
        Route::get('/wishlist', fn(Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'wishlist'))->name('wishlist');
        Route::get('/feed/{type?}', [LegacyCompatibilityController::class, 'feed'])->whereIn('type', ['properties', 'projects', 'posts']);
        Route::get('/account/properties', fn(string $locale) => redirect(auth()->check() ? '/admin' : '/' . $locale . '/login'));
        Route::get('/favorites/state', [FavoriteController::class, 'state'])->name('favorites.state');

        // Original design login/register/password pages.
        Route::middleware('guest')->group(function () {
            Route::get('/login', fn(Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'login'))->name('login');
            Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
            Route::get('/register', fn(Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'register'))->name('register');
            Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
            Route::get('/password/request', fn(Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'password/request'))->name('password.request');
            Route::post('/forgot-password', [AuthController::class, 'forgot'])->name('password.email')->middleware('throttle:5,1');
            Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
            Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
            Route::get('/auth/{provider}', fn(string $locale, string $provider) => redirect()->route('login', ['locale' => $locale]))->whereIn('provider', ['facebook', 'google'])->name('social.login');
        });

        // Localized compatibility endpoints found in the original HTML/JS.
        Route::post('/newsletter/subscribe', [LegacyCompatibilityController::class, 'newsletter'])->middleware('throttle:20,1');
        Route::post('/send-consult', [LegacyCompatibilityController::class, 'consult'])->middleware('throttle:10,1');
        Route::post('/contact/send', [LegacyCompatibilityController::class, 'contact'])->middleware('throttle:10,1');
        Route::post('/ajax/review/{slug}', [LegacyCompatibilityController::class, 'review'])->middleware('throttle:5,1');
        Route::match(['get', 'post'], '/ajax/render-ui-blocks', [LegacyCompatibilityController::class, 'renderUiBlocks'])->middleware('throttle:60,1');
        Route::get('/currency/switch/{code}', [LegacyCompatibilityController::class, 'currency']);
        Route::get('/ajax/properties/map', [PropertyMapController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/ajax/cities', [PropertyMapController::class, 'cities'])->middleware('throttle:60,1');
        Route::get('/ajax/projects/search', [LegacyCompatibilityController::class, 'projectSearch'])->middleware('throttle:60,1');
        Route::get('/map/governorates', [PropertyMapController::class, 'governorates'])->middleware('throttle:60,1');
        Route::get('/map/route', [PropertyMapController::class, 'route'])->middleware('throttle:30,1');

        // New backend endpoints remain available without changing the public design.
        Route::post('/leads', [LeadController::class, 'store'])->middleware('throttle:10,1')->name('leads.store');

        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/email/verify', fn() => view('auth.verify'))->name('verification.notice');
            Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
                $request->fulfill();
                return redirect()->route('home', ['locale' => app()->getLocale()]);
            })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
            Route::post('/email/verification-notification', function (Request $request) {
                $request->user()->sendEmailVerificationNotification();
                return back()->with('status', 'verification-link-sent');
            })->middleware('throttle:6,1')->name('verification.send');

            Route::post('/favorites/properties/{property}', [FavoriteController::class, 'store'])->name('favorites.store');
            Route::delete('/favorites/properties/{property}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
            Route::post('/favorites/merge', [FavoriteController::class, 'merge'])->name('favorites.merge');
            Route::post('/properties/{property}/reviews', [ReviewController::class, 'store'])->middleware('throttle:5,1')->name('reviews.store');

            Route::prefix('dashboard')->name('dashboard.')->group(function () {
                Route::get('/properties', [DashboardPropertyController::class, 'index'])->name('properties.index');
                Route::get('/properties/create', [DashboardPropertyController::class, 'create'])->name('properties.create');
                Route::post('/properties', [DashboardPropertyController::class, 'store'])->name('properties.store');
                Route::post('/properties/{property}/submit', [DashboardPropertyController::class, 'submit'])->name('properties.submit');
            });
        });

        // Exact static pages such as about-us, contact-us, faqs, pricing-plans, homepage-2...5.
        // Keep this last inside the locale group so backend routes above always win.
        Route::get('/{path}', [LegacyFrontendController::class, 'page'])
            ->where('path', '.*')
            ->name('legacy.page');
    });

// ---------------------------------------------------------------------------
// لوحة التحكم (CMS) — مسارات الإدارة كاملة، محمية بصلاحيات الأدوار.
// تُحمَّل قبل أي catch-all حتى لا تلتقطها صفحات الموقع العامة.
// ---------------------------------------------------------------------------
Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
});

Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

Route::prefix('admin')
    ->middleware(['web', 'admin.panel'])
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

// Keep original non-localized URLs alive (English mirror pages, feeds, old links).
Route::get('/{path}', [LegacyFrontendController::class, 'rootPage'])
    ->where('path', '^(?!admin(?:/|$)|api(?:/|$)|up$).+')
    ->name('legacy.root');
