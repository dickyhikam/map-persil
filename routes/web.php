<?php

use App\Http\Controllers\GeoJsonController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/satelit', function () {
    return view('grab');
});

Route::post('/save-updated-geojson', [GeoJsonController::class, 'saveUpdatedGeoJson']);
