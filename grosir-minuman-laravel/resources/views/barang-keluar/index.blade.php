@extends('layouts.app')
@section('title', 'Barang Keluar')

@section('content')

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="font-semibold mb-4 text-gray-700">Tambah Barang Keluar</h3>

    <form method="POST" action="{{ route('barang-keluar.store') }}"
          class="grid grid-cols-1 md:grid-cols-6 gap-4">
        @csrf

        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Barang <span class="text-red-500">*</span></label>
            <select name="barang_id" required
                class="w-full border rounded px-3 py-2 text-sm @error('barang_id') border-red-500 @enderror">
                <option value="">-- Pilih Barang --</option>
                @foreach ($barang as $b)
                    <option value="{{ $b->id }}" @selected(old('barang_id') == $b->id)>
                        {{ $b->kode_barang }} — {{ $b->nama }} (stok: {{ $b->stok }} pcs)
                    </option>
                @endforeach
            </select>
            @error('barang_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Jumlah (pcs) <span class="text-red-500">*</span></label>
            <input type="number" name="jumlah" min="1" value="{{ old('jumlah') }}" required
                class="w-full border rounded px-3 py-2 text-sm @error('jumlah') border-red-500 @enderror">
            @error('jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Keuntungan / pcs (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="keuntungan" min="0" value="{{ old('keuntungan') }}" required
                placeholder="Profit per pcs"
                class="w-full border rounded px-3 py-2 text-sm @error('keuntungan') border-red-500 @enderror">
            @error('keuntungan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm mb-1">Pembeli</label>
            <input type="text" name="pembeli" value="{{ old('pembeli') }}"
                placeholder="Nama toko / pelanggan"
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm mb-1">Status Bayar <span class="text-red-500">*</span></label>
            <select name="status_pembayaran" required
                class="w-full border rounded px-3 py-2 text-sm @error('status_pembayaran') border-red-500 @enderror">
                <option value="lunas"       @selected(old('status_pembayaran') === 'lunas')>Lunas</option>
                <option value="belum_lunas" @selected(old('status_pembayaran', 'belum_lunas') === 'belum_lunas')>Belum Lunas</option>
            </select>
        </div>

        <!-- <div class="md:col-span-4">
            <label class="block text-sm mb-1">Keterangan</label>
            <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                class="w-full border rounded px-3 py-2 text-sm">
        </div> -->

        <div class="md:col-span-6">
            <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded text-sm font-medium">
                Simpan
            </button>
            <span class="text-xs text-gray-500 ml-3">
                ℹ️ Total = (modal + keuntungan) × jumlah. Semua transaksi masuk ke
                <a href="{{ route('buku-tagihan.index') }}" class="text-emerald-600 hover:underline">Buku Tagihan</a>.
            </span>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b">
        <h3 class="font-semibold text-gray-700">Riwayat Barang Keluar</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Barang</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">Keuntungan</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-left">Pembeli</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($barangKeluar as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $row->barang->kode_barang }}</td>
                        <td class="px-4 py-3">{{ $row->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->barang->nama }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($row->jumlah) }} pcs</td>
                        <td class="px-4 py-3 text-right text-emerald-700">
                            Rp {{ number_format($row->keuntungan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            Rp {{ number_format($row->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">{{ $row->pembeli ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($row->status_pembayaran === 'lunas')
                                <span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Lunas</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Belum Lunas</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="{{ route('barang-keluar.destroy', $row->id) }}"
                                  onsubmit="return confirm('Yakin hapus? Stok akan dikembalikan.')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($barangKeluar->hasPages())
        <div class="px-6 py-4 border-t">{{ $barangKeluar->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
    const stokMap = @json($barang->pluck('stok', 'id'));
    document.querySelector('select[name="barang_id"]').addEventListener('change', function () {
        const stok = stokMap[this.value];
        if (stok !== undefined) {
            document.querySelector('input[name="jumlah"]').max = stok;
        }
    });
</script>
@endpush
@endsection