<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
| Route publik untuk probe Kubernetes dan uji beban (PRD 12.6).
| Sengaja TANPA grup middleware `web`: tidak ada session, cookie, CSRF,
| atau redirect ke /login, sehingga tidak ada tulis ke DB per request.
*/

// Liveness: tanpa akses DB.
Route::get('/health/live', fn () => response()->json(['status' => 'alive']));

// Readiness: 200 hanya jika DB siap.
Route::get('/health/ready', function () {
    try {
        DB::select('SELECT 1');

        return response()->json(['status' => 'ready']);
    } catch (Throwable $e) {
        report($e);

        return response()->json(['status' => 'not_ready'], 503);
    }
});

// Daftar tambak (read-only, tanpa data pribadi pemilik).
Route::get('/test/read-only/ponds', function () {
    $ponds = DB::table('ponds')
        ->select('pond_id', 'pond_code', 'pond_name', 'area_m2', 'operational_status')
        ->orderBy('pond_id')
        ->get();

    return response()->json(['count' => $ponds->count(), 'data' => $ponds]);
});

// Ringkasan agregat kelompok (read-only).
Route::get('/test/read-only/dashboard', function () {
    // Tambak aktif = di luar status `jeda` dan `tidak_aktif`.
    $activePonds = DB::table('ponds')
        ->whereNotIn('operational_status', ['jeda', 'tidak_aktif'])
        ->count();

    $harvestKg = DB::table('harvests')
        ->selectRaw("COALESCE(SUM(CASE WHEN unit = 'ton' THEN quantity * 1000 ELSE quantity END), 0) AS total_kg")
        ->value('total_kg');

    $openIssues = DB::table('issues')->where('status', 'terbuka')->count();

    return response()->json([
        'total_active_ponds' => $activePonds,
        'total_harvest_kg' => (float) $harvestKg,
        'open_issues' => $openIssues,
    ]);
});
