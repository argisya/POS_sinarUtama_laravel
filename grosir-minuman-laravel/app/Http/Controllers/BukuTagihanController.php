<?php
namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use App\Models\KasMasuk;
use Illuminate\Http\Request;

class BukuTagihanController extends Controller {
        public function index(Request $request) {
        $search = $request->input('q');
        $filter = $request->input('filter', 'semua');

        $barangKeluar = BarangKeluar::with('barang')
            ->when($search, fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('pembeli', 'like', "%{$search}%")
                  ->orWhereHas('barang', fn ($b) => $b->where('kode_barang', 'like', "%{$search}%"));
            }))
            ->when($filter === 'lunas', fn ($q) => $q->where('status_pembayaran', 'lunas'))
            ->when($filter === 'belum', fn ($q) => $q->where('status_pembayaran', 'belum_lunas'))
            ->latest('tanggal')->latest('id')
            ->paginate(15)->withQueryString();

        // --- PERHITUNGAN DINAMIS BERDASARKAN BOX ---
        $allTransaksi = BarangKeluar::with('barang')->get();

        $totalPiutang = $allTransaksi->where('status_pembayaran', 'belum_lunas')->sum(function ($row) {
            return $row->jumlah_box * $row->harga_jual;
        });

        $totalTagihanBulanIni = $allTransaksi->filter(function ($row) {
            return $row->tanggal->month === now()->month && $row->tanggal->year === now()->year;
        })->sum(function ($row) {
            return $row->jumlah_box * $row->harga_jual;
        });

        $jumlahBelumLunas = $allTransaksi->where('status_pembayaran', 'belum_lunas')->count();

        $totalKeuntungan = $allTransaksi->where('status_pembayaran', 'lunas')->sum('keuntungan');

        return view('buku-tagihan.index', compact(
            'barangKeluar', 'search', 'filter',
            'totalPiutang', 'totalTagihanBulanIni', 'jumlahBelumLunas', 'totalKeuntungan'
        ));
    }

        public function lunasi(Request $request, BarangKeluar $barangKeluar) {
        if ($barangKeluar->status_pembayaran === 'lunas') {
            return back()->with('error', 'Transaksi ini sudah lunas.');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($barangKeluar, $validated) {
            KasMasuk::create([
                'tanggal'          => $validated['tanggal'],
                'sumber'           => 'pelunasan_tagihan',
                'keterangan'       => 'Pelunasan - ' . ($barangKeluar->pembeli ?? $barangKeluar->barang->nama),
                'jumlah'           => $barangKeluar->jumlah_box * $barangKeluar->harga_jual, // ⬅️ Gunakan Box
                'barang_keluar_id' => $barangKeluar->id,
            ]);

            $barangKeluar->update(['status_pembayaran' => 'lunas']);
        });

        return redirect()->route('buku-tagihan.index')
            ->with('success', 'Tagihan berhasil dilunasi & kas masuk tercatat.');
    }

    public function destroy(BarangKeluar $barangKeluar) {
        \Illuminate\Support\Facades\DB::transaction(function () use ($barangKeluar) {
            // Kembalikan stok
            \App\Models\Barang::where('id', $barangKeluar->barang_id)
                ->increment('stok', $barangKeluar->jumlah);

            // FK cascade otomatis hapus semua kas_masuk terkait
            $barangKeluar->delete();
        });

        return redirect()->route('buku-tagihan.index')
            ->with('success', 'Transaksi berhasil dihapus, stok dikembalikan, dan kas masuk terkait ikut terhapus.');
    }
}