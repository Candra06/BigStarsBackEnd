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

    Route::get('dashboard-admin', 'API\AuthController@dashboardAdmin');
    Route::post('logout', 'API\AuthController@logout');
    Route::post('update-profil-admin', 'API\AuthController@updateProfileAdmin');
    Route::post('update-photo', 'API\AuthController@updateFoto');
    Route::prefix('guru')->group(function () {
        Route::post('/create', 'API\GuruController@store');
        Route::get('/data', 'API\GuruController@index');
        Route::post('/update/{id}', 'API\GuruController@update');
        Route::get('/detail/{id}', 'API\GuruController@show');
        Route::get('/delete/{id}', 'API\GuruController@destroy');
    });
    Route::prefix('mapel')->group(function () {
        Route::post('/create', 'API\MapelController@store');
        Route::get('/data', 'API\MapelController@index');
        Route::post('/update/{id}', 'API\MapelController@update');
        Route::get('/detail/{id}', 'API\MapelController@show');
        Route::get('/delete/{id}', 'API\MapelController@destroy');
    });
    Route::prefix('kelas')->group(function () {
        Route::get('/list-all', 'API\KelasController@index');
        Route::post('/create', 'API\KelasController@store');
        Route::post('/update/{id}', 'API\KelasController@update');
        Route::get('/detail/{id}', 'API\KelasController@show');
        Route::get('/kehadiran-kelas/{id}', 'API\KelasController@kehadiranByKelas');
        Route::get('/filter-kelas/{siswa}/{guru}/{status}', 'API\KelasController@filterKelas');
        Route::post('/add-absen-admin/{id}', 'API\MengajarController@update');
    });
    Route::prefix('siswa')->group(function () {
        Route::post('/create-wali', 'API\WalimuridController@store');
        Route::get('/show-wali', 'API\WalimuridController@index');
        Route::get('/detail-wali/{id}', 'API\WalimuridController@show');
        Route::post('/update-wali/{id}', 'API\WalimuridController@update');
        Route::get('/list', 'API\SiswaController@index');
        Route::post('/create-siswa', 'API\SiswaController@store');
        Route::get('/detail/{id}', 'API\SiswaController@show');
        Route::post('/update/{id}', 'API\SiswaController@update');
        Route::get('/delete/{id}', 'API\SiswaController@destroy');
    });
});
