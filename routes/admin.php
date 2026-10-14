<?php

/**
 * ---------------------------------------------------------------------------
 * لوحة التحكم (CMS) — كل مسارات الإدارة
 * ---------------------------------------------------------------------------
 * This file is loaded by routes/web.php under:
 *   prefix: /admin   name: admin.   middleware: web, admin.panel
 */

use App\Http\Controllers\Admin\AgencyController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CareerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitorLogController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// ------------------------------------------------------------------ الرئيسية
Route::get('/', DashboardController::class)->name('dashboard');

// ------------------------------------------------------------- الإحصائيات
Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
Route::get('/stats/properties', [StatsController::class, 'properties'])->name('stats.properties');
Route::get('/stats/users', [StatsController::class, 'users'])->name('stats.users');
Route::get('/stats/export', [StatsController::class, 'export'])->name('stats.export');

// ------------------------------------------------------------- سجل الزوار والمواقع
Route::prefix('visitors')->name('visitors.')->group(function (): void {
    Route::get('/', [VisitorLogController::class, 'index'])->name('index');
    Route::get('/{visitor}', [VisitorLogController::class, 'show'])->name('show');
    Route::delete('/{visitor}', [VisitorLogController::class, 'destroy'])->name('destroy');
});

// --------------------------------------------------------------- العقارات
Route::prefix('properties')->name('properties.')->group(function (): void {
    Route::get('/', [PropertyController::class, 'index'])->name('index');
    Route::get('/create', [PropertyController::class, 'create'])->name('create');
    Route::post('/', [PropertyController::class, 'store'])->name('store');
    Route::get('/pending', [PropertyController::class, 'pending'])->name('pending');
    Route::get('/featured', [PropertyController::class, 'featured'])->name('featured');
    Route::get('/assignments', [PropertyController::class, 'assignments'])->name('assignments');
    Route::post('/bulk', [PropertyController::class, 'bulk'])->name('bulk');
    Route::get('/{property}', [PropertyController::class, 'show'])->name('show');
    Route::get('/{property}/edit', [PropertyController::class, 'edit'])->name('edit');
    Route::put('/{property}', [PropertyController::class, 'update'])->name('update');
    Route::delete('/{property}', [PropertyController::class, 'destroy'])->name('destroy');
    Route::post('/{property}/approve', [PropertyController::class, 'approve'])->name('approve');
    Route::post('/{property}/reject', [PropertyController::class, 'reject'])->name('reject');
    Route::post('/{property}/feature', [PropertyController::class, 'toggleFeature'])->name('feature');
    Route::post('/{property}/assign', [PropertyController::class, 'assign'])->name('assign');
    Route::get('/{property}/stats', [PropertyController::class, 'stats'])->name('stats');
    Route::post('/{property}/media', [PropertyController::class, 'uploadMedia'])->name('media.store');
    Route::post('/{property}/media/{media}/primary', [PropertyController::class, 'primaryMedia'])->name('media.primary');
    Route::delete('/{property}/media/{media}', [PropertyController::class, 'destroyMedia'])->name('media.destroy');
});

// --------------------------------------------------------------- المشروعات
Route::prefix('projects')->name('projects.')->group(function (): void {
    Route::get('/', [ProjectController::class, 'index'])->name('index');
    Route::get('/create', [ProjectController::class, 'create'])->name('create');
    Route::post('/', [ProjectController::class, 'store'])->name('store');
    Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
    Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
    Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
});

// ------------------------------------------- المستخدمون والصلاحيات
Route::prefix('users')->name('users.')->group(function (): void {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/hierarchy', [UserController::class, 'hierarchy'])->name('hierarchy');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    Route::post('/{user}/status', [UserController::class, 'toggleStatus'])->name('status');
    Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
    Route::post('/{user}/password', [UserController::class, 'resetPassword'])->name('password');
    Route::post('/{user}/manager', [UserController::class, 'assignManager'])->name('manager');
});

Route::prefix('agents')->name('agents.')->group(function (): void {
    Route::get('/', [AgentController::class, 'index'])->name('index');
    Route::get('/create', [AgentController::class, 'create'])->name('create');
    Route::post('/', [AgentController::class, 'store'])->name('store');
    Route::get('/{agent}/edit', [AgentController::class, 'edit'])->name('edit');
    Route::put('/{agent}', [AgentController::class, 'update'])->name('update');
    Route::delete('/{agent}', [AgentController::class, 'destroy'])->name('destroy');
    Route::post('/{agent}/verify', [AgentController::class, 'toggleVerify'])->name('verify');
    Route::post('/{agent}/status', [AgentController::class, 'toggleStatus'])->name('status');
});

Route::prefix('roles')->name('roles.')->group(function (): void {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::get('/{role}', [RoleController::class, 'show'])->name('show');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    Route::post('/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('permissions');
});

// ------------------------------------------------------------- المحتوى
Route::prefix('pages')->name('pages.')->group(function (): void {
    Route::get('/', [PageController::class, 'index'])->name('index');
    Route::get('/create', [PageController::class, 'create'])->name('create');
    Route::post('/', [PageController::class, 'store'])->name('store');
    Route::get('/{page}/edit', [PageController::class, 'edit'])->name('edit');
    Route::put('/{page}', [PageController::class, 'update'])->name('update');
    Route::delete('/{page}', [PageController::class, 'destroy'])->name('destroy');
});

Route::prefix('faqs')->name('faqs.')->group(function (): void {
    Route::get('/', [FaqController::class, 'index'])->name('index');
    Route::get('/create', [FaqController::class, 'create'])->name('create');
    Route::post('/', [FaqController::class, 'store'])->name('store');
    Route::put('/settings', [FaqController::class, 'updateSettings'])->name('settings.update');
    Route::get('/{id}/edit', [FaqController::class, 'edit'])->name('edit');
    Route::put('/{id}', [FaqController::class, 'update'])->name('update');
    Route::delete('/{id}', [FaqController::class, 'destroy'])->name('destroy');
});

Route::prefix('posts')->name('posts.')->group(function (): void {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::get('/create', [PostController::class, 'create'])->name('create');
    Route::post('/', [PostController::class, 'store'])->name('store');
    Route::get('/{post}/edit', [PostController::class, 'edit'])->name('edit');
    Route::put('/{post}', [PostController::class, 'update'])->name('update');
    Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');
});

Route::prefix('careers')->name('careers.')->group(function (): void {
    Route::get('/', [CareerController::class, 'index'])->name('index');
    Route::get('/create', [CareerController::class, 'create'])->name('create');
    Route::post('/', [CareerController::class, 'store'])->name('store');
    Route::get('/applications', [CareerController::class, 'applications'])->name('applications');
    Route::get('/{career}/edit', [CareerController::class, 'edit'])->name('edit');
    Route::put('/{career}', [CareerController::class, 'update'])->name('update');
    Route::delete('/{career}', [CareerController::class, 'destroy'])->name('destroy');
    Route::post('/{career}/status', [CareerController::class, 'toggleStatus'])->name('status');
});

Route::prefix('testimonials')->name('testimonials.')->group(function (): void {
    Route::get('/', [TestimonialController::class, 'index'])->name('index');
    Route::get('/create', [TestimonialController::class, 'create'])->name('create');
    Route::post('/', [TestimonialController::class, 'store'])->name('store');
    Route::get('/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('edit');
    Route::put('/{testimonial}', [TestimonialController::class, 'update'])->name('update');
    Route::delete('/{testimonial}', [TestimonialController::class, 'destroy'])->name('destroy');
});

Route::prefix('locations')->name('locations.')->group(function (): void {
    Route::get('/', [LocationController::class, 'index'])->name('index');
    Route::post('/', [LocationController::class, 'store'])->name('store');
    Route::put('/{location}', [LocationController::class, 'update'])->name('update');
    Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');
});

Route::prefix('categories')->name('categories.')->group(function (): void {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

Route::prefix('amenities')->name('amenities.')->group(function (): void {
    Route::get('/', [AmenityController::class, 'index'])->name('index');
    Route::post('/', [AmenityController::class, 'store'])->name('store');
    Route::put('/{amenity}', [AmenityController::class, 'update'])->name('update');
    Route::delete('/{amenity}', [AmenityController::class, 'destroy'])->name('destroy');
});

Route::prefix('media')->name('media.')->group(function (): void {
    Route::get('/', [MediaController::class, 'index'])->name('index');
    Route::post('/', [MediaController::class, 'store'])->name('store');
    Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
});

// --------------------------------------------------- العملاء والمواعيد
Route::prefix('leads')->name('leads.')->group(function (): void {
    Route::get('/', [LeadController::class, 'index'])->name('index');
    Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
    Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
    Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
    Route::post('/{lead}/assign', [LeadController::class, 'assign'])->name('assign');
    Route::post('/{lead}/activity', [LeadController::class, 'activity'])->name('activity');
});

Route::prefix('appointments')->name('appointments.')->group(function (): void {
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
    Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
});

Route::prefix('reviews')->name('reviews.')->group(function (): void {
    Route::get('/', [ReviewController::class, 'index'])->name('index');
    Route::post('/{review}/approve', [ReviewController::class, 'approve'])->name('approve');
    Route::post('/{review}/reject', [ReviewController::class, 'reject'])->name('reject');
    Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
});

// ----------------------------------------------------------- الإشعارات
Route::prefix('notifications')->name('notifications.')->group(function (): void {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/create', [NotificationController::class, 'create'])->name('create');
    Route::post('/', [NotificationController::class, 'store'])->name('store');
    Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
    Route::put('/settings', [NotificationController::class, 'updateSettings'])->name('settings.update');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
});

// ---------------------------------------------------------- الإعدادات
Route::prefix('settings')->name('settings.')->group(function (): void {
    Route::get('/', [SettingController::class, 'general'])->name('index');
    Route::get('/general', [SettingController::class, 'general'])->name('general');
    Route::get('/maintenance', [SettingController::class, 'maintenance'])->name('maintenance');
    Route::get('/branding', [SettingController::class, 'branding'])->name('branding');
    Route::get('/contact', [SettingController::class, 'contact'])->name('contact');
    Route::get('/footer', [SettingController::class, 'footer'])->name('footer');
    Route::get('/services', [SettingController::class, 'services'])->name('services');
    Route::get('/partners', [SettingController::class, 'partners'])->name('partners');
    Route::get('/seo', [SettingController::class, 'seo'])->name('seo');
    Route::get('/mail', [SettingController::class, 'mail'])->name('mail');
    Route::get('/integrations', [SettingController::class, 'integrations'])->name('integrations');
    Route::get('/storage', [SettingController::class, 'storage'])->name('storage');
    Route::get('/properties', [SettingController::class, 'properties'])->name('properties');
    Route::get('/panel', [SettingController::class, 'panel'])->name('panel');
    Route::put('/{group}', [SettingController::class, 'update'])->name('update');
    Route::post('/test-connection', [SettingController::class, 'testConnection'])->name('test');
});

// ------------------------------------------------------------- النظام
Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');

// ------------------------------------------------------- الملف الشخصي
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
