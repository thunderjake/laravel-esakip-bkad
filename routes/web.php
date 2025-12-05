<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HakaksesController;
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

/*
|--------------------------------------------------------------------------
| ROUTES DENGAN AUTH
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | HOME & PROFILE
    |--------------------------------------------------------------------------
    */
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changepassword'])->name('profile.change-password');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

    // Export Word
    Route::get('/kpi/export-word', [KpiController::class, 'exportWord'])->name('kpi.exportWord');

    // Example Page
    Route::get('/blank-page', [HomeController::class, 'blank'])->name('blank');


    /*
    |--------------------------------------------------------------------------
    | SUPERADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware('superadmin')->prefix('hakakses')->name('hakakses.')->group(function () {
        Route::get('/', [HakaksesController::class, 'index'])->name('index');
        Route::get('/create', [HakaksesController::class, 'create'])->name('create');
        Route::post('/store', [HakaksesController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [HakaksesController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [HakaksesController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [HakaksesController::class, 'destroy'])->name('delete');
    });


    /*
    |--------------------------------------------------------------------------
    | E-SAKIP MODULE
    |--------------------------------------------------------------------------
    */
    Route::prefix('esakip')->name('esakip.')->group(function () {

        // Dashboard umum
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/kinerja', [DashboardController::class, 'kinerja'])->name('dashboard.kinerja');

        /*
        ----------------------------------------------------------------------
        | ROUTES YANG BUTUH ROLE (admin, superadmin, pimpinan)
        ----------------------------------------------------------------------
        */
        Route::middleware(['auth','role:admin,superadmin,pimpinan'])->group(function () {

            Route::get('/dashboard/ringkasan', [DashboardController::class, 'ringkasanKeseluruhan'])
                ->name('dashboard.ringkasan');

            // Tindak Lanjut (pembuatan / penyelesaian) - hanya untuk admin/pimpinan
            Route::post('/tindak-lanjut/store', [TindakLanjutKpiController::class, 'store'])
                ->name('tindak-lanjut.store');

            Route::post('/tindak-lanjut/{id}/selesai', [TindakLanjutKpiController::class, 'selesai'])
                ->name('tindak-lanjut.selesai');
        });


        /*
        ----------------------------------------------------------------------
        | Routes untuk menandai viewed (AJAX)
        | - Bisa diakses oleh user yang relevan (bidang/admin_bidang/kepala_bidang/pimpinan/admin/superadmin)
        ----------------------------------------------------------------------
        */
        Route::middleware(['auth','role:bidang,admin_bidang,kepala_bidang,pimpinan,admin,superadmin'])->group(function () {
            // Tandai satu tindak lanjut sebagai sudah dilihat (AJAX)
            Route::post('/tindak-lanjut/{id}/view', [TindakLanjutKpiController::class, 'markViewed'])
                ->name('tindak-lanjut.view');

            // Tandai beberapa tindak lanjut sebagai sudah dilihat (AJAX), body: { ids: [1,2,3] }
            Route::post('/tindak-lanjut/view-multiple', [TindakLanjutKpiController::class, 'markMultipleViewed'])
                ->name('tindak-lanjut.viewMultiple');
        });

        /*
        ----------------------------------------------------------------------
        | RESOURCE & HALAMAN UTAMA
        ----------------------------------------------------------------------
        */
        Route::resource('kpi', KpiController::class)->except(['show']);

        Route::get('/kinerja', [KinerjaController::class, 'index'])->name('kinerja');
        Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi');
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

        // Tambahan khusus KPI (filter / preview / print)
        Route::get('/kpi/report', [KpiController::class, 'report'])->name('kpi.report');
        Route::get('/kpi/report/print', [KpiController::class, 'reportPrint'])->name('kpi.reportPrint');

        // Cetak single KPI (aksi cepat)
        Route::get('/kpi/{id}/print', [KpiController::class, 'printSingle'])
            ->name('kpi.printSingle');

        // Tambahan lain seperti hierarki
        Route::get('/kpi/hierarki', [KpiController::class, 'hierarki'])->name('kpi.hierarki');

        // ---------------------------------------------------------------------
        // Route untuk fetch measurements sebagai JSON (dipakai oleh modal riwayat)
        // ---------------------------------------------------------------------
        Route::get('/kpi/{id}/measurements', [KpiController::class, 'measurementsJson'])
            ->name('kpi.measurements');

        /*
        ----------------------------------------------------------------------
        | Measurement (CRUD minimal)
        | - Akses dibatasi: bidang/admin_bidang/kepala_bidang/pimpinan/admin/superadmin
        ----------------------------------------------------------------------
        */
        Route::middleware(['auth','role:bidang,admin_bidang,kepala_bidang,pimpinan,admin,superadmin'])->group(function () {
            Route::post('/kpi/{kpi}/measurement', [KpiController::class, 'storeMeasurement'])
                ->name('kpi.measurement.store');

            Route::put('/kpi/measurement/{id}', [KpiController::class, 'updateMeasurement'])
                ->name('kpi.measurement.update');

            Route::delete('/kpi/measurement/{id}', [KpiController::class, 'destroyMeasurement'])
                ->name('kpi.measurement.destroy');
        });

    });
});
