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
                // ===== 1. Penjualan Hari Ini (Berdasarkan Box) =====
        $penjualanHariIni = BarangKeluar::with('barang')
            ->whereDate('tanggal', today())
            ->get()
            ->sum(function ($row) {
                return $row->jumlah_box * $row->harga_jual;
            });

        // ===== 1. Kas Masuk Bulan Ini =====
        $kasMasukBulanIni = KasMasuk::with('barangKeluar')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->get()
            ->sum(function ($row) {
                if ($row->sumber === 'penjualan_tunai' && $row->barangKeluar) {
                    return $row->barangKeluar->jumlah_box * $row->barangKeluar->harga_jual;
                }
                return $row->jumlah;
            });

        // ===== 2. Total Keseluruhan Pengeluaran Kas Keluar =====
        $totalKasKeluar = \App\Models\KasKeluar::sum('jumlah');

        // ===== 3. Saldo Kas Bulan Ini (Kas Masuk - Total Kas Keluar) =====
        $kasBulanIni = $kasMasukBulanIni - $totalKasKeluar;
        // ===== Total Keseluruhan Pengeluaran Kas Keluar =====
        $totalKasKeluar = \App\Models\KasKeluar::sum('jumlah');

        // ===== Perhitungan Sesuai Spesifikasi: Kas Masuk Hari Ini - Total Kas Keluar =====
        // $kasHariIni = $kasMasukHariIni - $totalKasKeluar;

        // Piutang = total transaksi yang belum lunas
        $allBarangMasuk = BarangMasuk::all();
            $totalPiutang = $allBarangMasuk->where('status_pembayaran', 'belum_lunas')->sum(function ($row) {
                return $row->jumlah_box * $row->harga_satuan;
            });

        // ===== UTANG: Barang Keluar yang Belum Lunas =====
        $allBarangKeluar = BarangKeluar::all();
        $totalUtang = $allBarangKeluar->where('status_pembayaran', 'belum_lunas')->sum('total_harga');

        // ===== 3. Total Keuntungan (Berdasarkan Box) =====
                // ===== Keuntungan =====
        // Keuntungan Kotor (Lunas)
        $totalKeuntunganKotor = BarangKeluar::where('status_pembayaran', 'lunas')->sum('keuntungan');
        // Pengeluaran
        $totalPengeluaran = \App\Models\KasKeluar::sum('jumlah');
        // Sisa Saldo (Bisa minus jika pengeluaran lebih besar)
        $totalKeuntungan = $totalKeuntunganKotor - $totalPengeluaran;  

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

        // --- MENGGUNAKAN COLLECTION ---
        $allBarangMasuk = BarangMasuk::all();

        $totalModal = $allBarangMasuk->sum(function ($row) {
            return $row->jumlah_box * $row->harga_satuan;
        });

        $totalModalLunas = $allBarangMasuk->where('status_pembayaran', 'lunas')->sum(function ($row) {
            return $row->jumlah_box * $row->harga_satuan;
        });

        $totalModalBelumLunas = $totalModal - $totalModalLunas;

        $totalModalBulanIni = $allBarangMasuk->filter(function ($row) {
            $tgl = \Carbon\Carbon::parse($row->tanggal);
            return $tgl->month === now()->month && $tgl->year === now()->year;
        })->sum(function ($row) {
            return $row->jumlah_box * $row->harga_satuan;
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
        // ===== Pengingat Jatuh Tempo =====
        $reminders = BarangMasuk::with('barang')
            ->where('status_pembayaran', 'belum_lunas')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'penjualanHariIni', 'kasBulanIni', 'totalPiutang','totalUtang',
            'stokMenipis', 'stokHabis',
            'chartLabels', 'chartPenjualan', 'totalMingguIni',
            'recentTransaksi', 'barangKritis',
            'totalModal', 'totalModalBulanIni', 'detailBarangMasuk',
            'totalModalLunas', 'totalModalBelumLunas',
            'reminders',
            'totalKeuntungan'
        ));
    }
}