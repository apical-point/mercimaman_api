<?php
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\TransientTokenController;
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

Route::post('oauth/token', [AccessTokenController::class, 'issueToken']);
Route::get('oauth/authorize', [AuthorizationController::class, 'authorize']);
Route::post('oauth/approve', [ApproveAuthorizationController::class, 'approve']);
Route::post('oauth/deny', [DenyAuthorizationController::class, 'deny']);
Route::post('oauth/token/refresh', [TransientTokenController::class, 'refresh']);


Route::get('/', function () {
    return view('welcome');
});
