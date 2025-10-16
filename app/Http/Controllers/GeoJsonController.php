<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GeoJsonController extends Controller
{
    public function saveUpdatedGeoJson(Request $request)
    {
        // Ambil data GeoJSON yang dikirim dari frontend
        $updatedGeoJsonData = $request->input();

        // Tentukan path file GeoJSON yang ada di folder public
        $filePath = public_path('LAMIPAK-BPN-PERSIL.geojson');

        // Periksa apakah file ada
        if (!File::exists($filePath)) {
            return response()->json(['message' => 'File GeoJSON tidak ditemukan.'], 404);
        }

        // Simpan data yang diperbarui ke file GeoJSON
        try {
            // Konversi data menjadi string dan simpan ke file
            File::put($filePath, json_encode($updatedGeoJsonData, JSON_PRETTY_PRINT));
            return response()->json(['message' => 'Data berhasil diperbarui.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui data: ' . $e->getMessage()], 500);
        }
    }
}
