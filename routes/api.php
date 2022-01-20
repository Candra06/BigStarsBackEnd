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
    Route::get('dashboard-guru', 'API\AuthController@dashboardGuru');
    Route::get('dashboard-wali', 'API\AuthController@dashboardWali');
    Route::post('logout', 'API\AuthController@logout');
    Route::post('update-profil-admin', 'API\AuthController@updateProfileAdmin');
    Route::post('update-profil-guru', 'API\AuthController@updateProfileGuru');
    Route::post('update-profil-wali', 'API\AuthController@updateProfileWali');
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
        Route::post('/add-jadwal/{id}', 'API\KelasController@addJadwal');
        Route::get('/delete-jadwal/{id}', 'API\KelasController@destroy');
        Route::post('/create', 'API\KelasController@store');
        Route::post('/update/{id}', 'API\KelasController@update');
        Route::get('/detail/{id}', 'API\KelasController@show');
        Route::get('/kehadiran-kelas/{id}', 'API\KelasController@kehadiranByKelas');
        Route::get('/filter-kelas/{siswa}/{guru}/{status}', 'API\KelasController@filterKelas');
        Route::post('/add-absen-admin/{id}', 'API\MengajarController@update');
        Route::post('/add-absen-guru/{idKelas}', 'API\MengajarController@store');
        Route::post('/sharing/{idKelas}', 'API\MengajarController@sharingKelas');
        Route::post('/updateKehadiran/{id}', 'API\MengajarController@updateKehadiranKelas');
        Route::get('/absensi/{id}', 'API\MengajarController@absensi');
    });
    Route::prefix('siswa')->group(function () {
        Route::post('/create-wali', 'API\WalimuridController@store');
        Route::post('/create-suswa-byWali', 'API\WalimuridController@create');
        Route::get('/show-wali', 'API\WalimuridController@index');
        Route::get('/detail-wali/{id}', 'API\WalimuridController@show');
        Route::post('/update-wali/{id}', 'API\WalimuridController@update');
        Route::get('/list', 'API\SiswaController@index');
        Route::post('/create-siswa', 'API\SiswaController@store');
        Route::get('/detail/{id}', 'API\SiswaController@show');
        Route::post('/update/{id}', 'API\SiswaController@update');
        Route::get('/delete/{id}', 'API\SiswaController@destroy');
    });
    Route::prefix('finance')->group(function () {
        Route::get('/index/{bulan}', 'API\PembayaranSppController@indexFinance');
        Route::get('/generate-fee', 'API\PembayaranFeeController@create');
        Route::get('/list-fee', 'API\PembayaranFeeController@index');
        Route::get('/generate-spp', 'API\PembayaranSppController@create');
        Route::get('/list-spp', 'API\PembayaranSppController@index');
        Route::get('/detail-spp/{id}', 'API\PembayaranSppController@show');
        Route::get('/detail-fee/{id}', 'API\PembayaranFeeController@show');
        Route::post('/confirm-fee/{id}', 'API\PembayaranFeeController@update');
        Route::post('/confirm-spp/{id}', 'API\PembayaranSppController@update');
        Route::get('/report', 'API\PembayaranFeeController@report');
        Route::get('/filter-fee/{nama}/{bulan}/{status}', 'API\PembayaranFeeController@filter');
        Route::get('/filter-spp/{nama}/{bulan}/{status}', 'API\PembayaranSppController@filter');
    });

    Route::prefix('notfikasi')->group(function () {
        Route::get('/list', 'API\NotifikasiController@index');
        Route::post('/read/{id}', 'API\NotifikasiController@readNotif');
    });
});
