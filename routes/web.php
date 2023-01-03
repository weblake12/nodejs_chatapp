<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware('auth')->group(function () {

    Route::get('/home/{id?}', 'HomeController@index')->name('home');
    Route::get('/users', 'HomeController@users')->name('users');
    Route::get('/users/connected', 'HomeController@users_connected')->name('users.connected');
    Route::get('/user/{user}', 'HomeController@users')->name('user');


    Route::get('/ajax/users/connected', 'HomeController@ajax_users_connected')->name('ajax.users_connected');
    Route::get('/ajax/count/messages', 'HomeController@ajax_count_messages')->name('ajax.count_messages');

});
