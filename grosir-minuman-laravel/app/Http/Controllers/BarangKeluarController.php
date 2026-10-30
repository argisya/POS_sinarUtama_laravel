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

        // Ambil data barang beserta total box masuk dan keluar
        $barang = Barang::withSum('barangMasuk as total_box_masuk', 'jumlah_box')
            ->withSum('barangKeluar as total_box_keluar', 'jumlah_box')
            ->findOrFail($validated['barang_id']);

        if ($barang->harga <= 0) {
            throw ValidationException::withMessages([
                'barang_id' => "Barang \"{$barang->nama}\" belum pernah masuk, modal belum diketahui.",
            ]);
        }

        // ⬇️ VALIDASI BARU: Berdasarkan Stok Tersedia (Box Masuk - Box Keluar) ⬇️
        $stokTersediaBox = ($barang->total_box_masuk ?? 0) - ($barang->total_box_keluar ?? 0);

        if ($stokTersediaBox < $validated['jumlah_box']) {
            throw ValidationException::withMessages([
                'jumlah_box' => "Stok tidak cukup. Stok saat ini: {$stokTersediaBox} Box.",
            ]);
        }
        // ⬆️ ------------------------------------------------------------- ⬆️

        // Hitung total pcs secara otomatis (untuk keperluan update kolom stok di DB)
        $validated['jumlah'] = $validated['jumlah_box'] * $barang->pcs_per_box;

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

            // Tetap kurangi kolom stok pcs di DB (sebagai arsip total pcs)
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

        public function update(Request $request, BarangKeluar $barangKeluar) {
        $validated = $request->validate([
            'barang_id'         => 'required|exists:barang,id',
            'jumlah_box'        => 'required|integer|min:1',
            'harga_jual'        => 'required|integer|min:1',
            'tanggal'           => 'required|date',
            'pembeli'           => 'nullable|string|max:100',
            'status_pembayaran' => 'required|in:lunas,belum_lunas',
            'keterangan'        => 'nullable|string|max:255',
        ]);

        $newBarang = Barang::withSum('barangMasuk as total_box_masuk', 'jumlah_box')
            ->withSum('barangKeluar as total_box_keluar', 'jumlah_box')
            ->findOrFail($validated['barang_id']);

        DB::transaction(function () use ($validated, $barangKeluar, $newBarang) {
            // 1. Kembalikan stok lama & hapus kas masuk lama
            Barang::where('id', $barangKeluar->barang_id)->increment('stok', $barangKeluar->jumlah);
            KasMasuk::where('barang_keluar_id', $barangKeluar->id)->delete();

            // 2. Validasi stok baru (Box Masuk - Box Keluar)
            // Kita tambahkan kembali box yang baru saja dikembalikan ke stok
            $stokTersediaBox = ($newBarang->total_box_masuk ?? 0) - ($newBarang->total_box_keluar ?? 0);
            if ($newBarang->id == $barangKeluar->barang_id) {
                $stokTersediaBox += $barangKeluar->jumlah_box; // Kembalikan jatah box lama untuk dicek ulang
            }

            if ($stokTersediaBox < $validated['jumlah_box']) {
                throw ValidationException::withMessages(['jumlah_box' => "Stok tidak cukup. Stok saat ini: {$stokTersediaBox} Box."]);
            }

            // 3. Hitung nilai baru
            $validated['jumlah'] = $validated['jumlah_box'] * $newBarang->pcs_per_box;
            $totalHarga = $validated['harga_jual'] * $validated['jumlah_box'];
            $modalPerBox = $newBarang->harga;
            $totalKeuntungan = ($validated['harga_jual'] - $modalPerBox) * $validated['jumlah_box'];

            // 4. Update data
            $barangKeluar->update(array_merge($validated, [
                'total_harga' => $totalHarga,
                'keuntungan'  => $totalKeuntungan,
            ]));

            // 5. Terapkan stok baru & buat kas masuk baru
            Barang::where('id', $newBarang->id)->decrement('stok', $validated['jumlah']);

            if ($validated['status_pembayaran'] === 'lunas') {
                $ket = "Penjualan {$newBarang->nama}";
                if (!empty($validated['pembeli'])) $ket .= " - {$validated['pembeli']}";
                KasMasuk::create([
                    'tanggal'          => $validated['tanggal'],
                    'sumber'           => 'penjualan_tunai',
                    'keterangan'       => $ket,
                    'jumlah'           => $totalHarga,
                    'barang_keluar_id' => $barangKeluar->id,
                ]);
            }
        });

        return redirect()->route('barang-keluar.index')->with('success', 'Transaksi berhasil diperbarui.');
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