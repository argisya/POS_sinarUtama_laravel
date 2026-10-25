<?php
namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use App\Models\KasMasuk;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BukuTagihanController extends Controller {
    public function index(Request $request) {
        $search = $request->input('q');
        $filter = $request->input('filter', 'semua'); // semua | belum | lunas

        $query = BarangKeluar::with('barang')
            ->withSum(['kasMasuk as total_paid' => function ($q) {
                $q->where('sumber', 'pelunasan_tagihan');
            }], 'jumlah')
            ->where('tipe_pembayaran', 'kredit')
            ->when($search, fn ($q) => $q->where('pembeli', 'like', "%{$search}%"))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        // Ambil semua dulu untuk filter status (karena status dihitung dari agregat)
        $all = $query->get();

        $all = $all->map(function ($row) {
            $row->total_paid = (int) ($row->total_paid ?? 0);
            $row->sisa       = max(0, $row->total_harga - $row->total_paid);
            $row->status     = $row->sisa <= 0
                ? 'lunas'
                : ($row->total_paid > 0 ? 'sebagian' : 'belum');
            return $row;
        });

        // Filter status
        if ($filter === 'lunas') {
            $all = $all->where('status', 'lunas');
        } elseif ($filter === 'belum') {
            $all = $all->whereIn('status', ['belum', 'sebagian']);
        }

        // Pagination manual (karena sudah pakai collection)
        $perPage     = 15;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $barangKeluar = new \Illuminate\Pagination\LengthAwarePaginator(
            $all->forPage($currentPage, $perPage)->values(),
            $all->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Ringkasan global (tidak terpengaruh filter)
        $base     = BarangKeluar::withSum(['kasMasuk as total_paid' => function ($q) {
                        $q->where('sumber', 'pelunasan_tagihan');
                    }], 'jumlah')
                    ->where('tipe_pembayaran', 'kredit')->get();

        $totalPiutang        = $base->sum(fn ($r) => max(0, $r->total_harga - ($r->total_paid ?? 0)));
        $totalTagihanBulanIni = $base->filter(fn ($r) => $r->tanggal->month === now()->month && $r->tanggal->year === now()->year)->sum('total_harga');
        $jumlahBelumLunas    = $base->filter(fn ($r) => ($r->total_paid ?? 0) < $r->total_harga)->count();
        $jumlahPelanggan     = $base->whereNotNull('pembeli')->unique('pembeli')->count();

        return view('buku-tagihan.index', compact(
            'barangKeluar', 'search', 'filter',
            'totalPiutang', 'totalTagihanBulanIni', 'jumlahBelumLunas', 'jumlahPelanggan'
        ));
    }

    public function storePayment(Request $request, BarangKeluar $barangKeluar) {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jumlah'  => 'required|integer|min:1',
        ]);

        if ($barangKeluar->tipe_pembayaran !== 'kredit') {
            return back()->with('error', 'Transaksi ini bukan kredit.');
        }

        $totalPaid = (int) $barangKeluar->pembayaranTagihan()->sum('jumlah');
        $sisa      = max(0, $barangKeluar->total_harga - $totalPaid);

        if ($sisa <= 0) {
            return back()->with('error', 'Tagihan ini sudah lunas.');
        }

        if ($validated['jumlah'] > $sisa) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah bayar melebihi sisa tagihan (Rp '
                    . number_format($sisa, 0, ',', '.') . ').',
            ]);
        }

        KasMasuk::create([
            'tanggal'          => $validated['tanggal'],
            'sumber'           => 'pelunasan_tagihan',
            'keterangan'       => 'Pelunasan - ' . ($barangKeluar->pembeli ?? $barangKeluar->barang->nama),
            'jumlah'           => $validated['jumlah'],
            'barang_keluar_id' => $barangKeluar->id,
        ]);

        return redirect()->route('buku-tagihan.index')
            ->with('success', 'Pembayaran berhasil dicatat.');
    }
}