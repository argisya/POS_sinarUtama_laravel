<?php
namespace App\Http\Controllers;

use App\Models\KasKeluar;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KasKeluarController extends Controller {
    public function index() {
        $kasKeluar = KasKeluar::latest('tanggal')->latest('id')->paginate(15);

        // Hitung Saldo Keuntungan
        $totalKeuntungan = BarangKeluar::where('status_pembayaran', 'lunas')->sum('keuntungan');
        $totalKasKeluar = KasKeluar::sum('jumlah');
        $sisaSaldo = $totalKeuntungan - $totalKasKeluar;

        return view('kas-keluar.index', compact('kasKeluar', 'totalKeuntungan', 'totalKasKeluar', 'sisaSaldo'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'keterangan' => 'required|string|max:255',
            'jumlah'     => 'required|integer|min:1',
        ]);

        // // Validasi: Cek sisa saldo keuntungan
        // $totalKeuntungan = BarangKeluar::where('status_pembayaran', 'lunas')->sum('keuntungan');
        // $totalKasKeluar = KasKeluar::sum('jumlah');
        // $sisaSaldo = $totalKeuntungan - $totalKasKeluar;

        // if ($validated['jumlah'] > $sisaSaldo) {
        //     throw ValidationException::withMessages([
        //         'jumlah' => 'Saldo keuntungan tidak cukup. Sisa saldo saat ini: Rp ' . number_format($sisaSaldo, 0, ',', '.'),
        //     ]);
        // }

        KasKeluar::create($validated);

        return redirect()->route('kas-keluar.index')
            ->with('success', 'Kas keluar berhasil dicatat & saldo keuntungan berkurang.');
    }

    public function destroy(KasKeluar $kasKeluar) {
        $kasKeluar->delete();
        return redirect()->route('kas-keluar.index')
            ->with('success', 'Catatan kas keluar berhasil dihapus.');
    }
}