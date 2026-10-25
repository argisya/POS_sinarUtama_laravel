<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class StokController extends Controller {
    public function index(Request $request) {
        $search = $request->input('q');
        $filter = $request->input('filter', 'semua'); // semua | aman | menipis | habis

        $query = Barang::query();

        // Pencarian nama
        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        // Filter status stok
        if ($filter === 'habis') {
            $query->where('stok', '<=', 0);
        } elseif ($filter === 'menipis') {
            $query->whereColumn('stok', '<=', 'stok_minimum')
                  ->where('stok', '>', 0);
        } elseif ($filter === 'aman') {
            $query->whereColumn('stok', '>', 'stok_minimum');
        }

        $barang = $query->orderBy('nama')->paginate(15)->withQueryString();

        // Ringkasan (dihitung dari SEMUA barang, tidak terpengaruh filter)
        $totalJenis     = Barang::count();
        $totalStok      = Barang::sum('stok');
        $jumlahMenipis  = Barang::whereColumn('stok', '<=', 'stok_minimum')
                                ->where('stok', '>', 0)->count();
        $jumlahHabis    = Barang::where('stok', '<=', 0)->count();

        return view('stok.index', compact(
            'barang', 'search', 'filter',
            'totalJenis', 'totalStok', 'jumlahMenipis', 'jumlahHabis'
        ));
    }
}