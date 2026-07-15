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
    Route::get('/file/{file}/download', [FileController::class, 'download'])->name('file.download');
    Route::get('/file/{file}/preview', [FileController::class, 'preview'])->name('file.preview');

    // Payments API Sync & Print
    Volt::route('/payments', 'payments.index')->name('payments.index');
    Route::post('/payments/sync/{id}', [\App\Http\Controllers\PaymentController::class, 'sync'])->name('payments.sync');
    Route::post('/payments/sync-batch', [\App\Http\Controllers\PaymentController::class, 'syncBatch'])->name('payments.sync-batch');
    Route::get('/payments/{payment}/print', [\App\Http\Controllers\PaymentController::class, 'print'])->name('payments.print');
    Route::post('/payments/{payment}/save-print', [\App\Http\Controllers\PaymentController::class, 'savePrint'])->name('payments.save-print');

    // Laporan SPN
    Volt::route('/laporan-spn', 'laporan-spn.index')->name('laporan-spn.index');
    Route::get('/laporan-spn/{payment}/print', [\App\Http\Controllers\PaymentController::class, 'printSpn'])->name('laporan-spn.print');

    // PDF to Image Converter
    Volt::route('/pdf-converter', 'pdf-converter')->name('pdf-converter');
    Volt::route('/pdf-compressor', 'pdf-compressor')->name('pdf-compressor');
    // Buku Agenda - Surat Masuk
    Volt::route('/surat-masuk', 'surat-masuk.index')->name('surat-masuk.index');
    Volt::route('/surat-masuk/create', 'surat-masuk.form')->name('surat-masuk.create');
    Volt::route('/surat-masuk/{id}/edit', 'surat-masuk.form')->name('surat-masuk.edit');
    Volt::route('/surat-masuk/{id}/disposisi', 'surat-masuk.disposisi')->name('surat-masuk.disposisi');

    // Buku Agenda - Surat Keluar
    Volt::route('/surat-keluar', 'surat-keluar.index')->name('surat-keluar.index');
    Volt::route('/surat-keluar/create', 'surat-keluar.form')->name('surat-keluar.create');
    Volt::route('/surat-keluar/{id}/edit', 'surat-keluar.form')->name('surat-keluar.edit');
});
