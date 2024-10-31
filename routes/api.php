<?php

use App\Http\Controllers\User\DayTransferController;
use App\Http\Controllers\User\LeaveRequestController;
use App\Http\Controllers\User\LeaveTypeController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'user', 'middleware' => []], function () {
        Route::post('/profile', [UserController::class, 'profile'])->name('profile');
        Route::post('/login', [UserController::class, 'login'])->name('login');
        Route::get('/updatePassword', [UserController::class, 'updatePassword']);
        Route::post('/remaining-days', [UserController::class, 'remainingDays'])->name('remainingDays');
        Route::post('/leave-request/status/{id}', [LeaveRequestController::class, 'changeStatus'])->name('change-status');
        Route::get('/leave-request/approve/{id}', [LeaveRequestController::class, 'approveStatus'])->name('approveStatus');
        Route::get('/leave-request/deny/{id}', [LeaveRequestController::class, 'denyStatus'])->name('denyStatus');
        Route::get('/leave-request/approved', [LeaveRequestController::class, 'getApproved'])->name('approved-request');
        Route::get('/managers', [UserController::class, 'getManagers']);
        Route::post('reset-password/{id}', [UserController::class, 'resetPassword']);
        Route::post('days-transfer/status/{id}', [DayTransferController::class, 'changeStatus']);
        Route::get('leave-request/approved/perWeek', [LeaveRequestController::class, 'perWeek']);
        Route::get('/leave-request/per-user/{id}', [LeaveRequestController::class, 'perUser'])->name('perUser');
        Route::get('/leave-request-archive', [LeaveRequestController::class, 'archive'])->name('archive');
        Route::apiResources([
            'leave-request' => LeaveRequestController::class,
            'leave-type' => LeaveTypeController::class,
            'users' => UserController::class,
            'days-transfer' => DayTransferController::class,
        ]);
        Route::get('/leave-type/new/emergency', [LeaveTypeController::class, 'emergency'])->name('emergency');

    });
});
