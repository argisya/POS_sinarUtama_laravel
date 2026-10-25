<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\KasMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BarangKeluarController extends Controller {
    public function index() {
        $barangKeluar = BarangKeluar::with('barang')
            ->latest('tanggal')
            ->latest('id')
            ->paginate(10);

        $barang = Barang::orderBy('nama')->get();

        return view('barang-keluar.index', compact('barangKeluar', 'barang'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'barang_id'       => 'required|exists:barang,id',
            'jumlah'          => 'required|integer|min:1',
            'tanggal'         => 'required|date',
            'pembeli'         => 'nullable|string|max:100',
            'tipe_pembayaran' => 'required|in:cash,kredit',
            'keterangan'      => 'nullable|string|max:255',
        ]);

        $barang = Barang::findOrFail($validated['barang_id']);

        if ($barang->stok < $validated['jumlah']) {
            throw ValidationException::withMessages([
                'jumlah' => "Stok tidak cukup. Stok {$barang->nama} saat ini: {$barang->stok} {$barang->satuan}.",
            ]);
        }

        $totalHarga = $barang->harga * $validated['jumlah'];

        DB::transaction(function () use ($validated, $barang, $totalHarga) {
            $bk = BarangKeluar::create(array_merge($validated, [
                'total_harga' => $totalHarga,
            ]));

            Barang::where('id', $validated['barang_id'])
                ->decrement('stok', $validated['jumlah']);

            if ($validated['tipe_pembayaran'] === 'cash') {
                $ket = "Penjualan {$barang->nama}";
                if (!empty($validated['pembeli'])) {
                    $ket .= " - {$validated['pembeli']}";
                }

                KasMasuk::create([
                    'tanggal'          => $validated['tanggal'],
                    'sumber'           => 'penjualan_tunai',
                    'keterangan'       => $ket,
                    'jumlah'           => $totalHarga,
                    'barang_keluar_id' => $bk->id,
                ]);
            }
        });

        return redirect()->route('barang-keluar.index')
            ->with('success', 'Barang keluar berhasil disimpan & stok berkurang.');
    }

    public function destroy(BarangKeluar $barangKeluar) {
        DB::transaction(function () use ($barangKeluar) {
            Barang::where('id', $barangKeluar->barang_id)
                ->increment('stok', $barangKeluar->jumlah);
            // kas_masuk akan otomatis terhapus via FK cascade
            $barangKeluar->delete();
        });

        return redirect()->route('barang-keluar.index')
            ->with('success', 'Data barang keluar dihapus & stok dikembalikan.');
    }
}