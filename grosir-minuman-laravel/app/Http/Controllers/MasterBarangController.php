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

        $totalBarang  = Barang::count();
        $totalStok    = Barang::sum('stok');
        $jumlahKritis = Barang::where(function ($q) {
            $q->where('stok', '<=', 0)
              ->orWhere(function ($w) {
                  $w->where('stok', '>', 0)
                    ->whereColumn('stok', '<=', 'stok_minimum');
              });
        })->count();

        return view('master-barang.index', compact(
            'barang', 'search', 'totalBarang', 'totalStok', 'jumlahKritis'
        ));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:barang,nama',
        ]);

        // Default lain diisi otomatis oleh model boot()
        Barang::create($validated);

        return redirect()->route('master-barang.index')
            ->with('success', 'Barang berhasil ditambahkan dengan kode otomatis.');
    }

    public function update(Request $request, Barang $masterBarang) {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100',
                       Rule::unique('barang', 'nama')->ignore($masterBarang->id)],
        ]);

        $masterBarang->update($validated);

        return redirect()->route('master-barang.index')
            ->with('success', 'Nama barang berhasil diperbarui.');
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