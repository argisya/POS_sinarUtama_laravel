@extends('layouts.app')
@section('title', 'Kas Masuk')

@section('content')

{{-- Ringkasan --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
        <p class="text-xs text-gray-500 uppercase">Kas Hari Ini</p>
        <p class="text-xl font-bold text-gray-800">
            Rp {{ number_format($totalHariIni, 0, ',', '.') }}
        </p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500">
        <p class="text-xs text-gray-500 uppercase">Kas Bulan Ini</p>
        <p class="text-xl font-bold text-gray-800">
            Rp {{ number_format($totalBulanIni, 0, ',', '.') }}
        </p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
        <p class="text-xs text-gray-500 uppercase">Total Kas</p>
        <p class="text-xl font-bold text-gray-800">
            Rp {{ number_format($totalSemua, 0, ',', '.') }}
        </p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-500">
        <p class="text-xs text-gray-500 uppercase">Jumlah Transaksi</p>
        <p class="text-xl font-bold text-gray-800">{{ number_format($jumlahTrx) }}</p>
    </div>
</div>

{{-- Form Tambah Manual --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-700">Catat Kas Masuk Manual</h3>
        <span class="text-xs text-gray-500">
            ℹ️ Penjualan tunai tercatat otomatis dari menu Barang Keluar.
        </span>
    </div>

    <form method="POST" action="{{ route('kas-masuk.store') }}"
          class="grid grid-cols-1 md:grid-cols-6 gap-4">
        @csrf

        <div>
            <label class="block text-sm mb-1">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                class="w-full border rounded px-3 py-2 text-sm @error('tanggal') border-red-500 @enderror">
            @error('tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Sumber <span class="text-red-500">*</span></label>
            <select name="sumber" required
                class="w-full border rounded px-3 py-2 text-sm @error('sumber') border-red-500 @enderror">
                <option value="pelunasan_tagihan" @selected(old('sumber') === 'pelunasan_tagihan')>Pelunasan Tagihan</option>
                <option value="modal"             @selected(old('sumber') === 'modal')>Modal</option>
                <option value="lain_lain"         @selected(old('sumber') === 'lain_lain')>Lain-lain</option>
            </select>
            @error('sumber') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Jumlah (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="jumlah" min="1" value="{{ old('jumlah') }}" required
                placeholder="0"
                class="w-full border rounded px-3 py-2 text-sm @error('jumlah') border-red-500 @enderror">
            @error('jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Keterangan</label>
            <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                placeholder="cth: Pelunasan Toko Jaya"
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div class="md:col-span-6">
            <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded text-sm font-medium">
                Simpan
            </button>
        </div>
    </form>
</div>

{{-- Filter --}}
<div class="bg-white rounded-lg shadow mb-4 p-4">
    <form method="GET" action="{{ route('kas-masuk.index') }}"
          class="flex flex-col md:flex-row gap-3 md:items-end">

        <div>
            <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $dari }}"
                class="border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $sampai }}"
                class="border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Sumber</label>
            <select name="sumber" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua</option>
                <option value="penjualan_tunai"   @selected($sumber === 'penjualan_tunai')>Penjualan Tunai</option>
                <option value="pelunasan_tagihan" @selected($sumber === 'pelunasan_tagihan')>Pelunasan Tagihan</option>
                <option value="modal"             @selected($sumber === 'modal')>Modal</option>
                <option value="lain_lain"         @selected($sumber === 'lain_lain')>Lain-lain</option>
            </select>
        </div>
        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium">
            Filter
        </button>
        @if ($dari || $sampai || $sumber)
            <a href="{{ route('kas-masuk.index') }}" class="text-sm text-gray-500 hover:underline px-2 pb-2">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="font-semibold text-gray-700">Riwayat Kas Masuk</h3>
        <span class="text-sm">
            Total periode ini:
            <b class="text-emerald-600">
                Rp {{ number_format($totalFiltered, 0, ',', '.') }}
            </b>
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Sumber</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-center">Tipe</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($kasMasuk as $row)
                    @php
                        $warnaSumber = match ($row->sumber) {
                            'penjualan_tunai'   => 'bg-emerald-100 text-emerald-700',
                            'pelunasan_tagihan' => 'bg-blue-100 text-blue-700',
                            'modal'             => 'bg-purple-100 text-purple-700',
                            default             => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $kasMasuk->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3">{{ $row->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded {{ $warnaSumber }}">
                                {{ $row->sumber_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $row->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-700">
                            Rp {{ number_format($row->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($row->barang_keluar_id)
                                <span class="text-xs text-gray-500" title="Dibuat otomatis">🔒 Auto</span>
                            @else
                                <span class="text-xs text-gray-500">Manual</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($row->sumber === 'penjualan_tunai')
                                <span class="text-xs text-gray-400" title="Hapus lewat menu Buku Tagihan">🔒 Terkunci</span>
                            @else
                                <form method="POST" action="{{ route('kas-masuk.destroy', $row->id) }}"
                                    class="inline"
                                    onsubmit="return confirm('Yakin hapus catatan kas ini?\n\n{{ $row->sumber === 'pelunasan_tagihan' ? 'Status transaksi akan kembali menjadi BELUM LUNAS.' : '' }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline text-xs">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Belum ada catatan kas masuk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($kasMasuk->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $kasMasuk->links() }}
        </div>
    @endif
</div>

@endsection