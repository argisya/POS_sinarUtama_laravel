<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller {
    public function index() {
        $barangMasuk = BarangMasuk::with('barang')
            ->latest('tanggal')->latest('id')
            ->paginate(10);

        $barang = Barang::orderBy('nama')->get();

        return view('barang-masuk.index', compact('barangMasuk', 'barang'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'barang_id'         => 'required|exists:barang,id',
            'jumlah'            => 'required|integer|min:1',
            'harga_satuan'      => 'required|integer|min:0',
            'tanggal'           => 'required|date',
            'supplier'          => 'nullable|string|max:100',
            'status_pembayaran' => 'required|in:lunas,belum_lunas',
        ]);

        DB::transaction(function () use ($validated) {
            BarangMasuk::create($validated);

            // Update stok + update modal terbaru di tabel barang
            Barang::where('id', $validated['barang_id'])
                ->increment('stok', $validated['jumlah']);

            if ($validated['harga_satuan'] > 0) {
                Barang::where('id', $validated['barang_id'])
                    ->update(['harga' => $validated['harga_satuan']]);
            }
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