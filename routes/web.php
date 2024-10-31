<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/user/init/password/{token}', [UserController::class, 'preparePassword'])->name('prepare.password');
Route::post('/user/set/password/{id}', [UserController::class, 'setPassword'])->name('set.password');
