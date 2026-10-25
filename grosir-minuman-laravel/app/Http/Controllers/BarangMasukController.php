<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller {
    public function index() {
        $barangMasuk = BarangMasuk::with('barang')
            ->latest('tanggal')
            ->latest('id')
            ->paginate(10);

        $barang = Barang::orderBy('nama')->get();

        return view('barang-masuk.index', compact('barangMasuk', 'barang'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'jumlah'    => 'required|integer|min:1',
            'tanggal'   => 'required|date',
            'supplier'  => 'nullable|string|max:100',
            'keterangan'=> 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            BarangMasuk::create($validated);
            Barang::where('id', $validated['barang_id'])
                ->increment('stok', $validated['jumlah']);
        });

        return redirect()->route('barang-masuk.index')
            ->with('success', 'Barang masuk berhasil disimpan & stok bertambah.');
    }

    public function destroy(BarangMasuk $barangMasuk) {
        DB::transaction(function () use ($barangMasuk) {
            Barang::where('id', $barangMasuk->barang_id)
                ->decrement('stok', $barangMasuk->jumlah);
            $barangMasuk->delete();
        });

        return redirect()->route('barang-masuk.index')
            ->with('success', 'Data barang masuk dihapus & stok disesuaikan.');
    }
}