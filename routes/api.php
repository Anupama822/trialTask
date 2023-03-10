<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', ['App\Http\Controllers\API\AuthApiController', 'register'])->name('register');
Route::post('/login', ['App\Http\Controllers\API\AuthApiController', 'login'])->name('login');
Route::get('/verifyUser/{id}', ['App\Http\Controllers\API\AuthApiController', 'verifyEmail'])->name('email.verify');

Route::group(['middleware' => ['auth:sanctum', 'apiUserVerified']], function(){
    Route::post('/change-password', ['App\Http\Controllers\API\AuthApiController', 'changePassword'])->name('changePassword');
    Route::post('/reset-password', ['App\Http\Controllers\API\AuthApiController', 'resetPassword'])->name('resetPassword');
    Route::get('/user/details', ['App\Http\Controllers\API\AuthApiController', 'getUserInfo'])->name('getUserInfo');
});