<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PropertyModerationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\Dashboard\PropertyController as DashboardPropertyController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LegacyCompatibilityController;
use App\Http\Controllers\LegacyFrontendController;
use App\Http\Controllers\LocaleController;
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
Route::get('/ajax/properties/map', [PropertyMapController::class, 'index'])->middleware('throttle:60,1');
Route::get('/ajax/cities', [PropertyMapController::class, 'cities'])->middleware('throttle:60,1');
Route::get('/ajax/projects/search', [LegacyCompatibilityController::class, 'projectSearch'])->middleware('throttle:60,1');

Route::prefix('{locale}')
    ->whereIn('locale', config('locales.supported'))
    ->middleware(['set.locale', 'set.currency'])
    ->group(function () {
        Route::get('/homepage-{variant}', function (string $locale) {
            return redirect('/' . $locale, 301);
        })->whereIn('variant', ['1', '2', '3', '4', '5']);

        // Public GET pages are rendered from the exact frontend files supplied by the user.
        Route::get('/', [LegacyFrontendController::class, 'home'])->name('home');
        Route::get('/properties', fn (Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'properties'))->name('properties.index');
        Route::get('/properties/{slug}', fn (Request $request, string $locale, string $slug) => app(LegacyFrontendController::class)->page($request, $locale, 'properties/' . $slug))->name('properties.show');
        Route::get('/projects', fn (Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'projects'))->name('projects.index');
        Route::get('/projects/{slug}', fn (Request $request, string $locale, string $slug) => app(LegacyFrontendController::class)->page($request, $locale, 'projects/' . $slug))->name('projects.show');
        Route::get('/agents/{slug}', fn (Request $request, string $locale, string $slug) => app(LegacyFrontendController::class)->page($request, $locale, 'agents/' . $slug))->name('agents.show');
        Route::get('/blog', [ContentController::class, 'blog'])->name('blog.index');
        Route::get('/blog/{slug}', [ContentController::class, 'post'])->name('blog.show');
        Route::get('/news/{slug}', fn (Request $request, string $locale, string $slug) => app(LegacyFrontendController::class)->page($request, $locale, 'news/' . $slug))->name('news.show');
        Route::get('/careers/{slug}', fn (Request $request, string $locale, string $slug) => app(LegacyFrontendController::class)->page($request, $locale, 'careers/' . $slug))->name('careers.show');
        Route::get('/wishlist', fn (Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'wishlist'))->name('wishlist');
        Route::get('/favorites/state', [FavoriteController::class, 'state'])->name('favorites.state');

        // Original design login/register/password pages.
        Route::middleware('guest')->group(function () {
            Route::get('/login', fn (Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'login'))->name('login');
            Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
            Route::get('/register', fn (Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'register'))->name('register');
            Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
            Route::get('/password/request', fn (Request $request, string $locale) => app(LegacyFrontendController::class)->page($request, $locale, 'password/request'))->name('password.request');
            Route::post('/forgot-password', [AuthController::class, 'forgot'])->name('password.email')->middleware('throttle:5,1');
            Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
            Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
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

        // New backend endpoints remain available without changing the public design.
        Route::post('/leads', [LeadController::class, 'store'])->middleware('throttle:10,1')->name('leads.store');

        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/email/verify', fn () => view('auth.verify'))->name('verification.notice');
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

Route::prefix('admin')
    ->middleware(['auth', 'role:super-admin,admin,property-manager'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/properties/pending', [PropertyModerationController::class, 'index'])->name('properties.pending');
        Route::post('/properties/{property}/approve', [PropertyModerationController::class, 'approve'])->name('properties.approve');
        Route::post('/properties/{property}/reject', [PropertyModerationController::class, 'reject'])->name('properties.reject');
    });

// Keep original non-localized URLs alive (English mirror pages, feeds, old links).
Route::get('/{path}', [LegacyFrontendController::class, 'rootPage'])
    ->where('path', '^(?!admin(?:/|$)|api(?:/|$)|up$).+')
    ->name('legacy.root');
