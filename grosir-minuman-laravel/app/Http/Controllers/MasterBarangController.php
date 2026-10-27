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

        return view('master-barang.index', compact('barang', 'search', 'totalBarang'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nama'        => 'required|string|max:100|unique:barang,nama',
            'pcs_per_box' => 'required|integer|min:1', 
        ]);

        Barang::create([
            'nama'        => $validated['nama'],
            'pcs_per_box' => $validated['pcs_per_box'],
            'stok'        => 0, // Stok awal 0
            'harga'       => 0, // Harga modal awal 0 (akan diisi saat Barang Masuk)
        ]);

        return redirect()->route('master-barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(Request $request, Barang $masterBarang) {
        $validated = $request->validate([
            'nama'        => ['required', 'string', 'max:100', Rule::unique('barang', 'nama')->ignore($masterBarang->id)],
            'pcs_per_box' => 'required|integer|min:1',
        ]);

        $masterBarang->update([
            'nama'        => $validated['nama'],
            'pcs_per_box' => $validated['pcs_per_box'],
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