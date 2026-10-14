<?php

use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\PropertyMapController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function (): void {
    Route::get('/properties/map', [PropertyMapController::class, 'index']);
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/{property}', [PropertyController::class, 'show']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    Route::prefix('notifications')->group(function (): void {
        Route::post('/fcm-token', [\App\Http\Controllers\Api\NotificationApiController::class, 'saveToken']);
        Route::get('/list', [\App\Http\Controllers\Api\NotificationApiController::class, 'list']);
        Route::post('/{id}/read', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAsRead']);
        Route::post('/mark-all-read', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAllAsRead']);
    });
});

Route::prefix('notifications')->middleware('throttle:60,1')->group(function (): void {
    Route::post('/fcm-token', [\App\Http\Controllers\Api\NotificationApiController::class, 'saveToken']);
    Route::get('/list', [\App\Http\Controllers\Api\NotificationApiController::class, 'list']);
    Route::post('/{id}/read', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAsRead']);
    Route::post('/mark-all-read', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAllAsRead']);
});

Route::prefix('visitors')->middleware('throttle:120,1')->group(function (): void {
    Route::post('/track', [\App\Http\Controllers\Api\VisitorTrackingApiController::class, 'track']);
    Route::post('/location', [\App\Http\Controllers\Api\VisitorTrackingApiController::class, 'updateLocation']);
});

