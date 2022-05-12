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


Route::namespace('App\Http\Controllers\Api')->group(function(){
	
	Route::post('login', 'LoginController@login');

	Route::middleware('api.auth')->group(function(){

		Route::middleware('api.admin')->prefix('user')->group(function(){
			Route::get('{id}', 'UserController@show');
			Route::post('store', 'UserController@store');
			Route::delete('delete/{id}', 'UserController@delete');
			Route::post('get-all', 'UserController@getAll');
		});

		Route::post('user/update-hobbies', 'UserController@updateHobbies')->middleware('api.user');

		Route::post('user/get-all-hobbies', 'UserController@getAllHobbies');
	});

});