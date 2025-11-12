<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ESakip\{
    DashboardController,
    KpiController,
    KinerjaController,
    EvaluasiController,
    MonitoringController,
    LaporanController,
    ProgramController,
    TindakLanjutKpiController
};

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // PROFILE
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changepassword'])->name('profile.change-password');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/kpi/export-word', [App\Http\Controllers\ESakip\KpiController::class, 'exportWord'])
    ->name('kpi.exportWord');
    Route::get('/esakip/kpi/report/print', [KpiController::class, 'reportPrint'])->name('esakip.kpi.reportPrint');
    // CONTOH
    Route::get('/blank-page', [App\Http\Controllers\HomeController::class, 'blank'])->name('blank');

    // Hak Akses (Superadmin Only)
    Route::middleware('superadmin')->group(function () {
        Route::get('/hakakses', [App\Http\Controllers\HakaksesController::class, 'index'])->name('hakakses.index');
        Route::get('/hakakses/edit/{id}', [App\Http\Controllers\HakaksesController::class, 'edit'])->name('hakakses.edit');
        Route::put('/hakakses/update/{id}', [App\Http\Controllers\HakaksesController::class, 'update'])->name('hakakses.update');
        Route::delete('/hakakses/delete/{id}', [App\Http\Controllers\HakaksesController::class, 'destroy'])->name('hakakses.delete');
        Route::get('/hakakses/create', [App\Http\Controllers\HakaksesController::class, 'create'])->name('hakakses.create');
        Route::post('/hakakses/store', [App\Http\Controllers\HakaksesController::class, 'store'])->name('hakakses.store');

    });

    // ESakip Module
  Route::prefix('esakip')->name('esakip.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/kinerja', [DashboardController::class, 'kinerja'])->name('dashboard.kinerja');

    Route::middleware(['auth', 'role:admin,superadmin,pimpinan'])->group(function () {
        Route::get('/dashboard/ringkasan', [DashboardController::class, 'ringkasanKeseluruhan'])
            ->name('dashboard.ringkasan');

        // ✅ Perbaikan di bawah
        Route::post('/tindak-lanjut/store', [TindakLanjutKpiController::class, 'store'])
            ->name('tindak-lanjut.store');
        Route::post('/tindak-lanjut/{id}/selesai', [TindakLanjutKpiController::class, 'selesai'])
            ->name('tindak-lanjut.selesai');
    });

    Route::resource('kpi', KpiController::class)->except(['show']);
    Route::get('/kinerja', [KinerjaController::class, 'index'])->name('kinerja');
    Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

    // tambahan khusus (di luar resource)
    Route::get('/kpi/report', [KpiController::class, 'report'])->name('kpi.report');
    Route::get('/kpi/hierarki', [KpiController::class, 'hierarki'])->name('kpi.hierarki');
});

});
