<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Koordinator\DashboardController;
use App\Http\Controllers\Koordinator\IssueController;
use App\Http\Controllers\Koordinator\MemberController;
use App\Http\Controllers\Koordinator\PondController as KoordinatorPondController;
use App\Http\Controllers\Koordinator\RecapController;
use App\Http\Controllers\Koordinator\RecordListController;
use App\Http\Controllers\Petambak\HistoryController;
use App\Http\Controllers\Petambak\HomeController;
use App\Http\Controllers\Petambak\PondController;
use App\Http\Controllers\Petambak\RecordController;
use Illuminate\Support\Facades\Route;

// Pintu masuk: arahkan sesuai role.
Route::get('/', function () {
    $member = auth()->user();
    if (! $member) {
        return redirect()->route('login');
    }

    return redirect($member->isKoordinatorAdmin() ? '/koordinator' : '/beranda');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Area petambak (FR-01, FR-02): hanya role petambak, dan hanya data tambak miliknya.
Route::middleware(['auth', 'role:petambak'])->group(function () {
    Route::get('/beranda', HomeController::class)->name('beranda');

    Route::get('/tambak', [PondController::class, 'index'])->name('ponds.index');
    Route::get('/tambak/{pond}', [PondController::class, 'show'])->whereNumber('pond')->name('ponds.show');

    Route::get('/catat', [RecordController::class, 'menu'])->name('catat');
    Route::get('/catat/panen', [RecordController::class, 'harvestForm'])->name('catat.panen');
    Route::post('/catat/panen', [RecordController::class, 'harvestStore']);
    Route::get('/catat/air', [RecordController::class, 'waterForm'])->name('catat.air');
    Route::post('/catat/air', [RecordController::class, 'waterStore']);
    Route::get('/catat/kendala', [RecordController::class, 'issueForm'])->name('catat.kendala');
    Route::post('/catat/kendala', [RecordController::class, 'issueStore']);
    Route::get('/catat/kegiatan', [RecordController::class, 'activityForm'])->name('catat.kegiatan');
    Route::post('/catat/kegiatan', [RecordController::class, 'activityStore']);
    Route::get('/catat/status', [PondController::class, 'statusForm'])->name('catat.status');
    Route::post('/catat/status', [PondController::class, 'updateStatus']);
    Route::get('/catat/siklus', [PondController::class, 'cycleForm'])->name('catat.siklus');
    Route::post('/catat/siklus', [PondController::class, 'storeCycle']);
    Route::get('/siklus/{cycle}/ubah', [PondController::class, 'cycleEdit'])->whereNumber('cycle')->name('cycles.edit');
    Route::put('/siklus/{cycle}', [PondController::class, 'cycleUpdate'])->whereNumber('cycle')->name('cycles.update');

    Route::get('/riwayat', HistoryController::class)->name('riwayat');
    Route::view('/akun', 'petambak.akun')->name('akun');
});

// Area koordinator/admin (gabungan koordinator dan admin pada MVP): F-03, F-04, F-05, F-12, F-13, F-14.
// Menu cuaca, peta interaktif, dan analisis pengelompokan sengaja tidak ada (di luar cakupan MVP).
Route::middleware(['auth', 'role:koordinator_admin'])->group(function () {
    Route::get('/koordinator', DashboardController::class)->name('koordinator');

    Route::prefix('koordinator')->name('k.')->group(function () {
        Route::resource('anggota', MemberController::class)->except('show')
            ->parameters(['anggota' => 'member'])->names('members')->whereNumber('member');
        Route::resource('tambak', KoordinatorPondController::class)
            ->parameters(['tambak' => 'pond'])->names('ponds')->whereNumber('pond');

        Route::get('/kendala', [IssueController::class, 'index'])->name('issues.index');
        Route::get('/kendala/{issue}', [IssueController::class, 'show'])->whereNumber('issue')->name('issues.show');
        Route::put('/kendala/{issue}', [IssueController::class, 'update'])->whereNumber('issue')->name('issues.update');

        Route::get('/siklus', [RecordListController::class, 'cycles'])->name('cycles');
        Route::get('/panen', [RecordListController::class, 'harvests'])->name('harvests');
        Route::get('/pengamatan-air', [RecordListController::class, 'waters'])->name('waters');

        Route::get('/rekap', RecapController::class)->name('rekap');
    });
});
