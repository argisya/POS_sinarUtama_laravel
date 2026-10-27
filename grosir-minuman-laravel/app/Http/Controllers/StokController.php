<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class StokController extends Controller {
        public function index(Request $request) {
        $search = $request->input('q');
        $filter = $request->input('filter', 'semua');

        // Sub-query untuk menghitung Stok Tersedia (Box Masuk - Box Keluar)
        $boxSql = '(COALESCE((SELECT SUM(jumlah_box) FROM barang_masuk WHERE barang_id = barang.id), 0) - COALESCE((SELECT SUM(jumlah_box) FROM barang_keluar WHERE barang_id = barang.id), 0))';

        $query = Barang::query()
            ->withSum('barangMasuk as total_box_masuk', 'jumlah_box')
            ->withSum('barangKeluar as total_box_keluar', 'jumlah_box')
            ->when($search, fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            }));

        // Filter berdasarkan Stok Tersedia (Box)
        if ($filter === 'habis') {
            $query->whereRaw("$boxSql <= 0");
        } elseif ($filter === 'tidak_aman') {
            $query->whereRaw("$boxSql > 0")->whereRaw("$boxSql <= 10");
        } elseif ($filter === 'aman') {
            $query->whereRaw("$boxSql > 10");
        }

        $barang = $query->orderBy('kode_barang')->paginate(15)->withQueryString();

        $totalJenis = Barang::count();
        $totalStokPcs = Barang::sum('stok');
        
        $totalStokBox = Barang::withSum('barangMasuk as total_box_masuk', 'jumlah_box')
            ->withSum('barangKeluar as total_box_keluar', 'jumlah_box')
            ->get()
            ->sum(function ($row) {
                return ($row->total_box_masuk ?? 0) - ($row->total_box_keluar ?? 0);
            });

        // Hitung jumlah card ringkasan atas berdasarkan Stok Tersedia
        $jumlahTidakAman = Barang::whereRaw("$boxSql > 0")->whereRaw("$boxSql <= 10")->count();
        $jumlahHabis = Barang::whereRaw("$boxSql <= 0")->count();

        return view('stok.index', compact(
            'barang', 'search', 'filter',
            'totalJenis', 'totalStokPcs', 'totalStokBox', 'jumlahTidakAman', 'jumlahHabis'
        ));
    }
}