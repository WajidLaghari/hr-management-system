<?php

use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PerformanceReviewController;
use App\Models\PerformanceReview;
use Illuminate\Support\Facades\Route;

/*employee routes maintained by Wajid*/
Route::group(['prefix' => 'auth'], function () {
    Route::controller(AuthController::class)->group(function(){
        Route::post('/login', 'login');
        Route::post('/hr-self-register', 'hrSelfRegister');
        Route::get('/logout', 'logout')->middleware('auth:sanctum');;
    });
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'is_admin']], function () {
    Route::controller(AuthController::class)->group(function(){
        Route::post('/hr-register', 'register');
        Route::get('/hrs', 'showAllHRS');
        Route::delete('/delete-hr/{id}', 'deleteHR');
        Route::put('/update-hr/{id}', 'updateHR');
        Route::get('/show-hr/{id}', 'showHR');
        Route::post('/approve-user/{id}', 'updateUserStatus');
    });
});

Route::group(['prefix' => 'hr', 'middleware' => ['auth:sanctum', 'is_admin_or_hr']], function () {
    Route::controller(AuthController::class)->group(function(){
        Route::post('/approve-user/{id}', 'updateUserStatus');
    });
});

Route::apiResource('performance-reviews', PerformanceReviewController::class)->middleware('auth:sanctum');

// employee routes maintained by naveed
Route::apiResource('employees', EmployeeController::class)->middleware('auth:sanctum');
