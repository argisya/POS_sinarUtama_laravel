<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterBarangController extends Controller {
        public function index(Request $request) {
        $search = $request->input('q');

        $barang = Barang::query()
            ->when($search, fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            }))
            ->orderBy('kode_barang')
            ->paginate(15)
            ->withQueryString();

        $totalBarang = Barang::count();
        
        // Hitung total stok keseluruhan dalam bentuk Pcs
        $totalStok = Barang::sum('stok'); 

        // Hitung total stok keseluruhan dalam bentuk Box
        $totalStokBox = Barang::all()->sum(function ($row) {
            return floor($row->stok / ($row->pcs_per_box ?: 1));
        });

        $jumlahKritis = Barang::where(function ($q) {
            $q->where('stok', '<=', 0)
              ->orWhere(function ($w) {
                  $w->where('stok', '>', 0)
                    ->whereColumn('stok', '<=', 'stok_minimum');
              });
        })->count();

        return view('master-barang.index', compact(
            'barang', 'search', 'totalBarang', 'totalStok', 'totalStokBox', 'jumlahKritis' // ⬅️ Tambahkan $totalStokBox
        ));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nama'        => 'required|string|max:100|unique:barang,nama',
            'qty_box'     => 'required|integer|min:0',
            'pcs_per_box' => 'required|integer|min:1', 
        ]);

        // Kalkulasi Total Pcs
        $totalStok = $validated['qty_box'] * $validated['pcs_per_box'];

        Barang::create([
            'nama'        => $validated['nama'],
            'pcs_per_box' => $validated['pcs_per_box'],
            'stok'        => $totalStok,
        ]);

        return redirect()->route('master-barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(Request $request, Barang $masterBarang) {
        $validated = $request->validate([
            'nama'        => ['required', 'string', 'max:100', Rule::unique('barang', 'nama')->ignore($masterBarang->id)],
            'qty_box'     => 'required|integer|min:0',
            'pcs_per_box' => 'required|integer|min:1',
        ]);

        $totalStok = $validated['qty_box'] * $validated['pcs_per_box'];

        $masterBarang->update([
            'nama'        => $validated['nama'],
            'pcs_per_box' => $validated['pcs_per_box'],
            'stok'        => $totalStok,
        ]);

        return redirect()->route('master-barang.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $masterBarang) {
        $adaMasuk  = $masterBarang->barangMasuk()->exists();
        $adaKeluar = $masterBarang->barangKeluar()->exists();

        if ($adaMasuk || $adaKeluar) {
            return redirect()->route('master-barang.index')
                ->with('error', 'Barang tidak bisa dihapus karena sudah memiliki riwayat transaksi.');
        }

        $masterBarang->delete();

        return redirect()->route('master-barang.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}