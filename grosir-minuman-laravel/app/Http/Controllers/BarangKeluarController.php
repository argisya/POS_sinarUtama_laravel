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
            ->latest('tanggal')->latest('id')
            ->paginate(10);

        $barang = Barang::withSum('barangMasuk as total_box_masuk', 'jumlah_box')
            ->withSum('barangKeluar as total_box_keluar', 'jumlah_box')
            ->orderBy('nama')
            ->get();


        // Hitung Grand Total (Harga Jual x Jumlah Box)
         $allBarangKeluar = BarangKeluar::all();
        $grandTotal = $allBarangKeluar->sum(function ($row) {
            return $row->harga_jual * $row->jumlah_box;
        });
        return view('barang-keluar.index', compact('barangKeluar', 'barang', 'grandTotal'));
    }

        public function store(Request $request) {
        $validated = $request->validate([
            'barang_id'         => 'required|exists:barang,id',
            'jumlah_box'        => 'required|integer|min:1',
            'harga_jual'        => 'required|integer|min:1', // Ini Harga Jual per BOX
            'tanggal'           => 'required|date',
            'pembeli'           => 'nullable|string|max:100',
            'status_pembayaran' => 'required|in:lunas,belum_lunas',
            'keterangan'        => 'nullable|string|max:255',
        ]);

        $barang = Barang::findOrFail($validated['barang_id']);

        if ($barang->harga <= 0) {
            throw ValidationException::withMessages([
                'barang_id' => "Barang \"{$barang->nama}\" belum pernah masuk, modal belum diketahui.",
            ]);
        }

        // Hitung total pcs secara otomatis
        $validated['jumlah'] = $validated['jumlah_box'] * $barang->pcs_per_box;

        if ($barang->stok < $validated['jumlah']) {
            throw ValidationException::withMessages([
                'jumlah_box' => "Stok tidak cukup. Stok saat ini: {$barang->stok} pcs.",
            ]);
        }

        // Perhitungan Finansial
        $totalHarga = $validated['harga_jual'] * $validated['jumlah_box'];
        
        // Hitung Keuntungan (Harga Jual Box - Modal Box)
        $modalPerBox = $barang->harga;
        $keuntunganPerBox = $validated['harga_jual'] - $modalPerBox;
        $totalKeuntungan = $keuntunganPerBox * $validated['jumlah_box'];

        DB::transaction(function () use ($validated, $barang, $totalHarga, $totalKeuntungan) {
            $bk = BarangKeluar::create(array_merge($validated, [
                'total_harga' => $totalHarga,
                'keuntungan'  => $totalKeuntungan,
            ]));

            Barang::where('id', $barang->id)->decrement('stok', $validated['jumlah']);

            if ($validated['status_pembayaran'] === 'lunas') {
                $ket = "Penjualan {$barang->nama}";
                if (!empty($validated['pembeli'])) $ket .= " - {$validated['pembeli']}";

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
            ->with('success', 'Barang keluar berhasil disimpan.');
    }

    public function destroy(BarangKeluar $barangKeluar) {
        DB::transaction(function () use ($barangKeluar) {
            Barang::where('id', $barangKeluar->barang_id)
                ->increment('stok', $barangKeluar->jumlah);
            $barangKeluar->delete();
        });

        return redirect()->route('barang-keluar.index')
            ->with('success', 'Data barang keluar dihapus & stok dikembalikan.');
    }
}