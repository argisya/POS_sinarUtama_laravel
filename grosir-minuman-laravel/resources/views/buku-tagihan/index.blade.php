@extends('layouts.app')
@section('title', 'Buku Tagihan')

@section('content')

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
        <p class="text-xs text-gray-500 uppercase">Total Piutang</p>
        <p class="text-xl font-bold text-red-600">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
        <p class="text-xs text-gray-500 uppercase">Tagihan Bulan Ini</p>
        <p class="text-xl font-bold text-gray-800">Rp {{ number_format($totalTagihanBulanIni, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
        <p class="text-xs text-gray-500 uppercase">Belum Lunas</p>
        <p class="text-xl font-bold text-amber-600">{{ number_format($jumlahBelumLunas) }} tagihan</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
        <p class="text-xs text-gray-500 uppercase">Total Keuntungan (Lunas)</p>
        <p class="text-xl font-bold text-emerald-600">Rp {{ number_format($totalKeuntungan, 0, ',', '.') }}</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow mb-4 p-4">
    <form method="GET" action="{{ route('buku-tagihan.index') }}" class="flex flex-col md:flex-row gap-3 md:items-center">
        <div class="flex-1">
            <input type="text" name="q" value="{{ $search }}"
                placeholder="Cari pembeli / kode barang..."
                class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <select name="filter" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
            <option value="semua" @selected($filter === 'semua')>Semua</option>
            <option value="belum" @selected($filter === 'belum')>Belum Lunas</option>
            <option value="lunas" @selected($filter === 'lunas')>Lunas</option>
        </select>
        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium">Cari</button>
        @if ($search || $filter !== 'semua')
            <a href="{{ route('buku-tagihan.index') }}" class="text-sm text-gray-500 hover:underline px-2">Reset</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="font-semibold text-gray-700">Semua Transaksi Barang Keluar</h3>
        <span class="text-xs text-gray-500">{{ $barangKeluar->total() }} transaksi</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Pembeli</th>
                    <th class="px-4 py-3 text-left">Barang</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Keuntungan</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($barangKeluar as $row)
                    <tr class="hover:bg-gray-50 {{ $row->status_pembayaran === 'belum_lunas' ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 font-mono text-xs">{{ $row->barang->kode_barang }}</td>
                        <td class="px-4 py-3">{{ $row->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->pembeli ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row->barang->nama }}</td>
                        <td class="px-4 py-3 text-right">{{ $row->jumlah }} pcs</td>
                        <td class="px-4 py-3 text-right font-semibold">
                            Rp {{ number_format($row->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-emerald-700">
                            Rp {{ number_format($row->keuntungan * $row->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($row->status_pembayaran === 'lunas')
                                <span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Lunas</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Belum Lunas</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($row->status_pembayaran === 'belum_lunas')
                                <form method="POST" action="{{ route('buku-tagihan.lunasi', $row->id) }}"
                                      onsubmit="return confirm('Tandai lunas & catat ke kas masuk?')">
                                    @csrf
                                    <input type="hidden" name="tanggal" value="{{ date('Y-m-d') }}">
                                    <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-xs">
                                        Lunasi
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">✓ Lunas</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($barangKeluar->hasPages())
        <div class="px-6 py-4 border-t">{{ $barangKeluar->links() }}</div>
    @endif
</div>

{{-- Modal Pembayaran --}}
<dialog id="modalBayar"
    class="rounded-lg shadow-2xl backdrop:bg-black/50 p-0 w-full max-w-md">
    <form method="POST" id="formBayar" class="p-6">
        @csrf
        <h3 class="text-lg font-semibold mb-4">Bayar Tagihan</h3>

        <div id="infoTagihan" class="text-sm text-gray-600 mb-4 bg-gray-50 rounded p-3"></div>

        <div class="mb-3">
            <label class="block text-sm mb-1">Tanggal Bayar <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm mb-1">Jumlah Bayar (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="jumlah" id="inputJumlah" min="1" required
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        @error('jumlah')
            <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
        @enderror

        <div class="flex gap-2 mt-6">
            <button type="submit"
                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium">
                Bayar
            </button>
            <button type="button"
                onclick="document.getElementById('modalBayar').close()"
                class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded text-sm">
                Batal
            </button>
        </div>
    </form>
</dialog>

<script>
    function bukaBayar(id, pembeli, sisa) {
        const form = document.getElementById('formBayar');
        form.action = `/buku-tagihan/${id}/bayar`;

        const input = document.getElementById('inputJumlah');
        input.max = sisa;
        input.value = sisa;

        document.getElementById('infoTagihan').innerHTML =
            `<div><b>Pembeli:</b> ${pembeli}</div>
             <div class="mt-1"><b>Sisa tagihan:</b>
                <span class="text-red-600 font-semibold">Rp ${sisa.toLocaleString('id-ID')}</span>
             </div>`;

        document.getElementById('modalBayar').showModal();
    }

    @if ($errors->has('jumlah'))
        // Buka lagi modal kalau ada error validasi
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('modalBayar').showModal();
        });
    @endif
</script>

@endsection