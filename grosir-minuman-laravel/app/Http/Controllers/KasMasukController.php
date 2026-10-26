<?php
namespace App\Http\Controllers;

use App\Models\KasMasuk;
use Illuminate\Http\Request;

class KasMasukController extends Controller {
    public function index(Request $request) {
        $dari   = $request->input('dari');
        $sampai = $request->input('sampai');
        $sumber = $request->input('sumber');

        $query = KasMasuk::query()
            ->when($dari,   fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($sumber, fn ($q) => $q->where('sumber', $sumber))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        // Hitung total dari query yang sudah difilter (untuk ditampilkan di footer)
        $totalFiltered = (clone $query)->sum('jumlah');

        $kasMasuk = $query->paginate(15)->withQueryString();

        // Ringkasan global (tidak terpengaruh filter)
        $totalHariIni  = KasMasuk::whereDate('tanggal', today())->sum('jumlah');
        $totalBulanIni = KasMasuk::whereMonth('tanggal', now()->month)
                                 ->whereYear('tanggal', now()->year)
                                 ->sum('jumlah');
        $totalSemua    = KasMasuk::sum('jumlah');
        $jumlahTrx     = KasMasuk::count();

        return view('kas-masuk.index', compact(
            'kasMasuk', 'dari', 'sampai', 'sumber', 'totalFiltered',
            'totalHariIni', 'totalBulanIni', 'totalSemua', 'jumlahTrx'
        ));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'sumber'     => 'required|in:pelunasan_tagihan,modal,lain_lain',
            'jumlah'     => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        KasMasuk::create($validated);

        return redirect()->route('kas-masuk.index')
            ->with('success', 'Kas masuk berhasil dicatat.');
    }

    public function destroy(KasMasuk $kasMasuk) {
    // Auto dari penjualan tunai → tidak bisa dihapus dari sini
        if ($kasMasuk->sumber === 'penjualan_tunai') {
            return redirect()->route('kas-masuk.index')
                ->with('error', 'Kas dari penjualan tunai harus dihapus lewat menu Buku Tagihan.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($kasMasuk) {
            // Kalau pelunasan → kembalikan status transaksi jadi belum_lunas
            if ($kasMasuk->sumber === 'pelunasan_tagihan' && $kasMasuk->barang_keluar_id) {
                $bk = $kasMasuk->barangKeluar;

                if ($bk) {
                    // Cek apakah masih ada pelunasan lain (defensif)
                    $masihAda = KasMasuk::where('barang_keluar_id', $bk->id)
                        ->where('sumber', 'pelunasan_tagihan')
                        ->where('id', '!=', $kasMasuk->id)
                        ->exists();

                    if (!$masihAda) {
                        $bk->update(['status_pembayaran' => 'belum_lunas']);
                    }
                }
            }

            $kasMasuk->delete();
        });

        return redirect()->route('kas-masuk.index')
            ->with('success', 'Kas masuk berhasil dihapus.');
    }
}