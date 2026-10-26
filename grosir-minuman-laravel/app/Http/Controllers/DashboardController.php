<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\KasMasuk;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {
    public function index() {
        // ===== 4 Kartu Metrik =====
        $penjualanHariIni = BarangKeluar::whereDate('tanggal', today())
                            ->sum('total_harga');

        $kasHariIni = KasMasuk::whereDate('tanggal', today())->sum('jumlah');

        // Piutang = total transaksi yang belum lunas
        $totalPiutang = BarangKeluar::where('status_pembayaran', 'belum_lunas')->sum('total_harga');

        $stokMenipis = Barang::whereColumn('stok', '<=', 'stok_minimum')
                        ->where('stok', '>', 0)->count();
        $stokHabis   = Barang::where('stok', '<=', 0)->count();

        // ===== Grafik 7 Hari Terakhir =====
        \Carbon\Carbon::setLocale('id');   // pastikan label bulan Indonesia

        $startDate = now()->subDays(6)->startOfDay();
        $endDate   = now()->endOfDay();

        // Satu query untuk 7 hari (jauh lebih efisien dari 7 query)
        $penjualanPerHari = BarangKeluar::selectRaw('DATE(tanggal) as tgl, SUM(total_harga) as total')
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        $chartLabels    = [];
        $chartPenjualan = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl   = now()->subDays($i)->startOfDay();
            $key   = $tgl->toDateString();
            $chartLabels[]    = $tgl->translatedFormat('d M');
            $chartPenjualan[] = (int) ($penjualanPerHari[$key] ?? 0);
        }

        $totalMingguIni = array_sum($chartPenjualan);

        // ===== Transaksi Terbaru (gabungan masuk & keluar) =====
        $recentKeluar = BarangKeluar::with('barang')
            ->latest('tanggal')->latest('id')
            ->take(5)->get()
            ->map(fn ($r) => [
                'tanggal'    => $r->tanggal,
                'tipe'       => 'keluar',
                'barang'     => $r->barang->nama ?? '-',
                'jumlah'     => $r->jumlah,
                'satuan'     => $r->barang->satuan ?? '',
                'total'      => $r->total_harga,
                'pembayaran' => $r->status_pembayaran,   // ⬅️ kolom baru
            ])
            ->toBase();   // ⬅️ convert ke base Collection

        $recentMasuk = BarangMasuk::with('barang')
            ->latest('tanggal')->latest('id')
            ->take(5)->get()
            ->map(fn ($r) => [
                'tanggal'    => \Carbon\Carbon::parse($r->tanggal),
                'tipe'       => 'masuk',
                'barang'     => $r->barang->nama ?? '-',
                'jumlah'     => $r->jumlah,
                'satuan'     => $r->barang->satuan ?? '',
                'total'      => 0,
                'pembayaran' => null,
            ])
            ->toBase();   // ⬅️ convert ke base Collection

        // --- MENGGUNAKAN COLLECTION (ANTI GAGAL) ---
            $allBarangMasuk = BarangMasuk::all();

            $totalModal = $allBarangMasuk->sum(function ($row) {
                return $row->jumlah * $row->harga_satuan;
            });

            $totalModalLunas = $allBarangMasuk->where('status_pembayaran', 'lunas')->sum(function ($row) {
                return $row->jumlah * $row->harga_satuan;
            });

            $totalModalBelumLunas = $totalModal - $totalModalLunas;

            $totalModalBulanIni = $allBarangMasuk->filter(function ($row) {
                $tgl = \Carbon\Carbon::parse($row->tanggal);
                return $tgl->month === now()->month && $tgl->year === now()->year;
            })->sum(function ($row) {
                return $row->jumlah * $row->harga_satuan;
            });

            $detailBarangMasuk = BarangMasuk::with('barang')
                ->latest('tanggal')->latest('id')
                ->take(30)
                ->get();

        $recentTransaksi = $recentKeluar
            ->merge($recentMasuk)
            ->sortByDesc(fn ($t) => \Carbon\Carbon::parse($t['tanggal'])->timestamp)
            ->take(8)
            ->values();

        // ===== Barang Stok Menipis / Habis (maks 6) =====
        $barangKritis = Barang::whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('stok')
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'penjualanHariIni', 'kasHariIni', 'totalPiutang',
            'stokMenipis', 'stokHabis',
            'chartLabels', 'chartPenjualan', 'totalMingguIni',
            'recentTransaksi', 'barangKritis',
            'totalModal', 'totalModalBulanIni', 'detailBarangMasuk',
            'totalModalLunas', 'totalModalBelumLunas' 
        ));
    }
}