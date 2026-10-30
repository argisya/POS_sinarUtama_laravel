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

        // --- MENGGUNAKAN COLLECTION ---
        $allBarangMasuk = BarangMasuk::all();
        
        // Perhitungan Grand Total: jumlah_box * harga_satuan (harga per box)
        $grandTotal = $allBarangMasuk->sum(function ($row) {
            return $row->jumlah_box * $row->harga_satuan;
        });

        $grandTotalLunas = $allBarangMasuk->where('status_pembayaran', 'lunas')->sum(function ($row) {
            return $row->jumlah_box * $row->harga_satuan;
        });

        $grandTotalBelumLunas = $grandTotal - $grandTotalLunas;

        return view('barang-masuk.index', compact(
            'barangMasuk', 'barang',
            'grandTotal', 'grandTotalLunas', 'grandTotalBelumLunas'
        ));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'barang_id'            => 'required|exists:barang,id',
            'jumlah_box'           => 'required|integer|min:1',
            'harga_satuan'         => 'required|integer|min:1', // Ini harga per BOX
            'tanggal'              => 'required|date',
            'tanggal_jatuh_tempo'  => 'nullable|date|after_or_equal:tanggal',
            'supplier'             => 'nullable|string|max:100',
            'status_pembayaran'    => 'required|in:lunas,belum_lunas',
        ]);

        $barang = Barang::findOrFail($validated['barang_id']);

        // Hitung total pcs secara otomatis
        $validated['jumlah'] = $validated['jumlah_box'] * $barang->pcs_per_box;

        DB::transaction(function () use ($validated, $barang) {
            BarangMasuk::create($validated);

            Barang::where('id', $validated['barang_id'])
                ->increment('stok', $validated['jumlah']);

            if ($validated['harga_satuan'] > 0) {
                // SIMPAN HARGA BOX LANGSUNG
                Barang::where('id', $validated['barang_id'])->update(['harga' => $validated['harga_satuan']]);
            }
        });

        return redirect()->route('barang-masuk.index')
            ->with('success', 'Barang masuk berhasil disimpan & stok bertambah.');
    }

        public function update(Request $request, BarangMasuk $barangMasuk) {
        $validated = $request->validate([
            'barang_id'            => 'required|exists:barang,id',
            'jumlah_box'           => 'required|integer|min:1',
            'harga_satuan'         => 'required|integer|min:1',
            'tanggal'              => 'required|date',
            'tanggal_jatuh_tempo'  => 'nullable|date|after_or_equal:tanggal',
            'supplier'             => 'nullable|string|max:100',
            'status_pembayaran'    => 'required|in:lunas,belum_lunas',
        ]);

        $newBarang = Barang::findOrFail($validated['barang_id']);
        
        DB::transaction(function () use ($validated, $barangMasuk, $newBarang) {
            // 1. Kembalikan stok lama
            Barang::where('id', $barangMasuk->barang_id)->decrement('stok', $barangMasuk->jumlah);

            // 2. Hitung total pcs baru
            $validated['jumlah'] = $validated['jumlah_box'] * $newBarang->pcs_per_box;

            // 3. Update data
            $barangMasuk->update($validated);

            // 4. Tambahkan stok baru
            Barang::where('id', $newBarang->id)->increment('stok', $validated['jumlah']);

            // 5. Update modal di master barang jika harganya berubah
            if ($validated['harga_satuan'] > 0) {
                Barang::where('id', $newBarang->id)->update(['harga' => $validated['harga_satuan']]);
            }
        });

        return redirect()->route('barang-masuk.index')->with('success', 'Transaksi berhasil diperbarui.');
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