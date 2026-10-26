<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class StokController extends Controller {
    public function index(Request $request) {
        $search = $request->input('q');
        $filter = $request->input('filter', 'semua'); // semua | aman | tidak_aman | habis

        $query = Barang::query()
            ->when($search, fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            }));

        if ($filter === 'habis') {
            $query->where('stok', '<=', 0);
        } elseif ($filter === 'tidak_aman') {
            $query->where('stok', '>', 0)
                  ->whereColumn('stok', '<=', 'stok_minimum');
        } elseif ($filter === 'aman') {
            $query->whereColumn('stok', '>', 'stok_minimum');
        }

        $barang = $query->orderBy('kode_barang')->paginate(15)->withQueryString();

        $totalJenis       = Barang::count();
        $totalStok        = Barang::sum('stok');
        
        // Hitung Total Box Keseluruhan
        $totalStokBox = Barang::all()->sum(function($row) {
            return floor($row->stok / ($row->pcs_per_box ?: 1));
        });

        $jumlahTidakAman  = Barang::where('stok', '>', 0)
                                ->whereColumn('stok', '<=', 'stok_minimum')->count();
        $jumlahHabis      = Barang::where('stok', '<=', 0)->count();

        return view('stok.index', compact(
            'barang', 'search', 'filter',
            'totalJenis', 'totalStok', 'totalStokBox', 'jumlahTidakAman', 'jumlahHabis' // ⬅️ Tambah $totalStokBox
        ));
    }
}