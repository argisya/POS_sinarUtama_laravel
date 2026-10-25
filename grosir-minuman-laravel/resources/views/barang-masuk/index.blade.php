@extends('layouts.app')
@section('title', 'Barang Masuk')

@section('content')

{{-- Form Tambah --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="font-semibold mb-4 text-gray-700">Tambah Barang Masuk</h3>

    <form method="POST" action="{{ route('barang-masuk.store') }}"
          class="grid grid-cols-1 md:grid-cols-6 gap-4">
        @csrf

        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Barang <span class="text-red-500">*</span></label>
            <select name="barang_id" required
                class="w-full border rounded px-3 py-2 text-sm @error('barang_id') border-red-500 @enderror">
                <option value="">-- Pilih Barang --</option>
                @foreach ($barang as $b)
                    <option value="{{ $b->id }}" @selected(old('barang_id') == $b->id)>
                        {{ $b->nama }} ({{ $b->satuan }})
                    </option>
                @endforeach
            </select>
            @error('barang_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Jumlah <span class="text-red-500">*</span></label>
            <input type="number" name="jumlah" min="1" value="{{ old('jumlah') }}" required
                class="w-full border rounded px-3 py-2 text-sm @error('jumlah') border-red-500 @enderror">
            @error('jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                class="w-full border rounded px-3 py-2 text-sm @error('tanggal') border-red-500 @enderror">
            @error('tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Supplier</label>
            <input type="text" name="supplier" value="{{ old('supplier') }}"
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm mb-1">Keterangan</label>
            <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div class="md:col-span-6">
            <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded text-sm font-medium">
                Simpan
            </button>
        </div>
    </form>
</div>

{{-- Tabel Riwayat --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b">
        <h3 class="font-semibold text-gray-700">Riwayat Barang Masuk</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Barang</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-left">Supplier</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($barangMasuk as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $barangMasuk->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->barang->nama }}</td>
                        <td class="px-4 py-3 text-right">
                            {{ number_format($row->jumlah) }} {{ $row->barang->satuan }}
                        </td>
                        <td class="px-4 py-3">{{ $row->supplier ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST"
                                  action="{{ route('barang-masuk.destroy', $row->id) }}"
                                  onsubmit="return confirm('Yakin hapus data ini? Stok akan dikurangi otomatis.')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Belum ada data barang masuk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($barangMasuk->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $barangMasuk->links() }}
        </div>
    @endif
</div>

@endsection