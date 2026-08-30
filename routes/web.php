<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public - redirect to login or dashboard
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// Auth routes (Volt-based)
Volt::route('/login', 'auth.login')->name('login');
Volt::route('/register', 'auth.register')->name('register');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Backup Database
    Route::middleware('role:superadmin,admin')->get('/backup-database', [\App\Http\Controllers\BackupController::class, 'downloadServerBackup'])->name('backup.database');

    // Dashboard
    Volt::route('/dashboard', 'dashboard')->name('dashboard');

    // Bundles
    Volt::route('/bundles', 'bundles.index')->name('bundles.index');
    Volt::route('/bundles/create', 'bundles.create')->name('bundles.create');
    Volt::route('/bundles/{bundle}', 'bundles.show')->name('bundles.show');
    Volt::route('/bundles/{bundle}/detail', 'bundles.detail')->name('bundles.detail');
    Volt::route('/bundles/{bundle}/print-label', 'bundles.print-label')->name('bundles.print-label');

    // Kategori (within bundle)
    Volt::route('/bundles/{bundle}/kategori/{kategori}', 'bundles.kategori-show')->name('kategori.show');

    // Dokumen
    Volt::route('/dokumen/{dokumen}', 'dokumen.show')->name('dokumen.show');
    Volt::route('/dokumen/{dokumen}/print', 'dokumen.print')->name('dokumen.print');

    // Pencarian
    Volt::route('/pencarian', 'pencarian')->name('pencarian');

    // File download & preview (protected)
    Route::get('/file/{encrypted_id}/download', [FileController::class, 'download'])->name('file.download');
    Route::get('/file/{encrypted_id}/preview', [FileController::class, 'preview'])->name('file.preview');

    // Payments API Sync & Print
    Volt::route('/payments', 'payments.index')->name('payments.index');
    Route::middleware('role:superadmin,admin')->post('/payments/sync/{id}', [\App\Http\Controllers\PaymentController::class, 'sync'])->name('payments.sync');
    Route::middleware('role:superadmin,admin')->post('/payments/sync-batch', [\App\Http\Controllers\PaymentController::class, 'syncBatch'])->name('payments.sync-batch');
    Route::get('/payments/{payment}/print', [\App\Http\Controllers\PaymentController::class, 'print'])->name('payments.print');
    Route::post('/payments/{payment}/save-print', [\App\Http\Controllers\PaymentController::class, 'savePrint'])->name('payments.save-print');

    // Laporan SPN
    Volt::route('/laporan-spn', 'laporan-spn.index')->name('laporan-spn.index');
    Route::get('/laporan-spn/print-batch', [\App\Http\Controllers\PaymentController::class, 'printSpnBatch'])->name('laporan-spn.print-batch');
    Route::get('/laporan-spn/print-arsip', [\App\Http\Controllers\PaymentController::class, 'printArsip'])->name('laporan-spn.print-arsip');
    Route::post('/laporan-spn/print-arsip/save', [\App\Http\Controllers\PaymentController::class, 'saveArsipBatch'])->name('laporan-spn.print-arsip.save');
    Route::get('/laporan-spn/{payment}/print', [\App\Http\Controllers\PaymentController::class, 'printSpn'])->name('laporan-spn.print');

    // PDF to Image Converter
    Volt::route('/pdf-converter', 'pdf-converter')->name('pdf-converter');
    Volt::route('/pdf-compressor', 'pdf-compressor')->name('pdf-compressor');
    // Buku Agenda - Surat Masuk
    Volt::route('/surat-masuk', 'surat-masuk.index')->name('surat-masuk.index');
    Route::middleware('role:superadmin,admin')->get('/surat-masuk/export', [\App\Http\Controllers\SuratMasukExcelController::class, 'export'])->name('surat-masuk.export');
    Route::middleware('role:superadmin,admin')->post('/surat-masuk/import', [\App\Http\Controllers\SuratMasukExcelController::class, 'import'])->name('surat-masuk.import');
    Volt::route('/surat-masuk/create', 'surat-masuk.form')->name('surat-masuk.create');
    Volt::route('/surat-masuk/{encrypted_id}/edit', 'surat-masuk.form')->name('surat-masuk.edit');
    Volt::route('/surat-masuk/{encrypted_id}/disposisi', 'surat-masuk.disposisi')->name('surat-masuk.disposisi');

    // Buku Agenda - Surat Keluar
    Volt::route('/surat-keluar', 'surat-keluar.index')->name('surat-keluar.index');
    Route::middleware('role:superadmin,admin')->get('/surat-keluar/export', [\App\Http\Controllers\SuratKeluarExcelController::class, 'export'])->name('surat-keluar.export');
    Route::middleware('role:superadmin,admin')->post('/surat-keluar/import', [\App\Http\Controllers\SuratKeluarExcelController::class, 'import'])->name('surat-keluar.import');
    Route::get('/surat-keluar/print-batch', [\App\Http\Controllers\SuratKeluarController::class, 'printBatch'])->name('surat-keluar.print-batch');
    Volt::route('/surat-keluar/create', 'surat-keluar.form')->name('surat-keluar.create');
    Volt::route('/surat-keluar/{encrypted_id}/edit', 'surat-keluar.form')->name('surat-keluar.edit');
});

// API Endpoint (Secured via api.token middleware)
Route::prefix('api')->middleware('auth:sanctum')->group(function () {
    
    Route::get('/reses', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\SuratMasuk::with('bundle', 'dokumen.fileAttachments')
            ->where('is_reses', true);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('no_surat', 'like', "%{$search}%")
                  ->orWhere('perihal', 'like', "%{$search}%")
                  ->orWhere('asal_surat', 'like', "%{$search}%");
            });
        }

        $reses = $query->orderBy('tanggal', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'total' => $reses->count(),
            'data' => $reses
        ]);
    });

    Route::get('/surat-masuk', function () {
        $data = \App\Models\SuratMasuk::with('bundle', 'dokumen.fileAttachments')->orderBy('tanggal', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'total' => $data->count(),
            'data' => $data
        ]);
    });

    Route::get('/surat-keluar', function () {
        $data = \App\Models\SuratKeluar::with('bundle', 'dokumen.fileAttachments')->orderBy('tanggal', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'total' => $data->count(),
            'data' => $data
        ]);
    });

    Route::get('/pekerjaan-sda/map', function () {
        $data = \App\Models\Payment::all();
        return response()->json([
            'status' => 'success',
            'total' => $data->count(),
            'data' => $data
        ]);
    });

});

