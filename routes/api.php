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

Route::post('/login', 'API\AuthController@login');
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('guru')->group(function () {
        Route::post('/create', 'API\GuruController@store');
        Route::get('/data', 'API\GuruController@index');
        Route::post('/update/{id}', 'API\GuruController@update');
        Route::get('/detail/{id}', 'API\GuruController@show');
        Route::get('/delete/{id}', 'API\GuruController@destroy');
    });
});
