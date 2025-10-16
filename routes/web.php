<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeoJsonController;
use App\Http\Controllers\MapsController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('auth');
// });

Route::get('/satelit', function () {
    return view('grab');
});

Route::get('/', [AuthController::class, 'index'])->name('pageAuth');
Route::get('/maps', [MapsController::class, 'index'])->name('pageMaps');
Route::post('/save-updated-geojson', [GeoJsonController::class, 'saveUpdatedGeoJson']);
