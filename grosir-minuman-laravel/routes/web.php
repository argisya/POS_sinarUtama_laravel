<?php
use App\Http\Controllers\BarangMasukController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\KasMasukController;
use App\Http\Controllers\BukuTagihanController;
use App\Http\Controllers\DashboardController;

// ===== Dashboard =====
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
// Barang Masuk Routes
Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');
Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->name('barang-masuk.store');
Route::delete('/barang-masuk/{barangMasuk}', [BarangMasukController::class, 'destroy'])->name('barang-masuk.destroy');
// barang Keluar Routes
Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])->name('barang-keluar.index');
Route::post('/barang-keluar', [BarangKeluarController::class, 'store'])->name('barang-keluar.store');
Route::delete('/barang-keluar/{barangKeluar}', [BarangKeluarController::class, 'destroy'])->name('barang-keluar.destroy');
// Stok Routes
Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
// Kas Masuk Routes
Route::get('/kas-masuk', [KasMasukController::class, 'index'])->name('kas-masuk.index');
Route::post('/kas-masuk', [KasMasukController::class, 'store'])->name('kas-masuk.store');
Route::delete('/kas-masuk/{kasMasuk}', [KasMasukController::class, 'destroy'])->name('kas-masuk.destroy');
// Buku Tagihan Routes
Route::get('/buku-tagihan', [BukuTagihanController::class, 'index'])->name('buku-tagihan.index');
Route::post('/buku-tagihan/{barangKeluar}/lunasi', [BukuTagihanController::class, 'lunasi'])
    ->name('buku-tagihan.lunasi');