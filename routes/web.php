<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/verification/{username}/{email}/{password}', [UserController::class, 'verify']);
Route::get('/login', [UserController::class, 'login']);
Route::get('/signup', [UserController::class, 'signup']);
Route::get('/reset/{token}', function($slug){
    return view('passwordReset', ["token"=>$slug]);
});
Route::get('reset/passwordReset/{token}', [UserController::class, 'reset']);
Route::get('/passwordForgot', [UserController::class, 'forgotPassword']);
Route::get('/profileEdit/{name}', [UserController::class, 'editProfile']);
Route::get('/profileUpdate', [UserController::class, 'updateProfile']);
Route::get('delete/{name}', [UserController::class, 'delete']);
Route::get('search/{email}', [UserController::class, 'search']);
Route::get('/signupPage', function () {
    return view('signup');
});