@extends('layouts.app')
@section('title', 'Master Barang')

@section('content')

{{-- Ringkasan --}}
{{-- Ringkasan (Sederhana) --}}
<div class="mb-4">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500 inline-block w-full md:w-1/3">
        <p class="text-xs text-gray-500 uppercase">Total Katalog Barang</p>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalBarang) }} <span class="text-sm font-normal">Produk</span></p>
    </div>
</div>

{{-- Form Tambah --}}
<div class="bg-white rounded-lg shadow p-6 mb-4">
    <h3 class="font-semibold mb-4 text-gray-700">Tambah Barang Baru</h3>

    <form method="POST" action="{{ route('master-barang.store') }}"
          class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @csrf

        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Nama Barang <span class="text-red-500">*</span></label>
            <input type="text" name="nama" id="inputNama" value="{{ old('nama') }}" required placeholder="cth: Aqua 600ml"
                class="w-full border rounded px-3 py-2 text-sm @error('nama') border-red-500 @enderror">
            @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Box</label>
            <input type="number" name="qty_box" id="inputQtyBox" value="1" readonly
                class="w-full border rounded px-3 py-2 text-sm bg-gray-100 cursor-not-allowed text-center font-bold text-gray-600">
        </div>

        <div>
            <label class="block text-sm mb-1">Isi per Box (Pcs) <span class="text-red-500">*</span></label>
            <input type="number" name="pcs_per_box" id="inputPcsPerBox" value="{{ old('pcs_per_box', 12) }}" min="1" required
                class="w-full border rounded px-3 py-2 text-sm @error('pcs_per_box') border-red-500 @enderror">
            @error('pcs_per_box') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Total Pcs (Otomatis)</label>
            <input type="number" id="inputTotalPcs" readonly
                class="w-full border rounded px-3 py-2 text-sm bg-gray-100 cursor-not-allowed font-bold text-gray-700">
        </div>

        <div class="flex items-end">
            <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded text-sm font-medium">
                Tambah Barang
            </button>
        </div>
        {{-- Input Box & Pcs --}}
        <!-- <div class="md:col-span-1">
            <label class="block text-sm mb-1">Kemasan (Box = Pcs) <span class="text-red-500">*</span></label>
            <div class="flex items-center gap-2">
                <input type="number" name="box_master" value="{{ old('box_master', 1) }}" min="1" required
                    class="w-16 border rounded px-2 py-2 text-sm text-center">
                <span class="text-sm text-gray-600">Box =</span>
                <input type="number" name="pcs_master" value="{{ old('pcs_master', 12) }}" min="1" required
                    class="w-20 border rounded px-2 py-2 text-sm text-center">
                <span class="text-sm text-gray-600">Pcs</span>
            </div>
            @error('box_master') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            @error('pcs_master') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div> -->
    </form>

    <div class="mt-3 text-xs text-gray-500 bg-gray-50 rounded p-3">
        ℹ️ Kode barang dibuat otomatis (BRG-0001, BRG-0002, dst).<br>
        Harga modal diisi lewat menu <a href="{{ route('barang-masuk.index') }}" class="text-emerald-600 hover:underline font-medium">Barang Masuk</a>.
        Stok awal = 0. Satuan = pcs. Stok minimum = 5.
    </div>
</div>

{{-- Pencarian --}}
<div class="bg-white rounded-lg shadow mb-4 p-4">
    <form method="GET" action="{{ route('master-barang.index') }}" class="flex gap-3">
        <input type="text" name="q" value="{{ $search }}"
            placeholder="Cari kode / nama barang..."
            class="flex-1 border rounded px-3 py-2 text-sm">
        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium">
            Cari
        </button>
        @if ($search)
            <a href="{{ route('master-barang.index') }}"
               class="text-sm text-gray-500 hover:underline px-2 py-2">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="font-semibold text-gray-700">Daftar Barang</h3>
        <span class="text-xs text-gray-500">{{ $barang->total() }} barang</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Barang</th>
                    <th class="px-4 py-3 text-center">Kemasan</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($barang as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $row->kode_barang }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row->nama }}</td>
                        
                        <td class="px-4 py-3 text-center text-gray-600 text-xs">
                            1 Box = {{ $row->pcs_per_box }} Pcs
                        </td>

                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <button type="button" onclick="bukaEdit({{ $row->id }}, @js($row->nama), {{ $row->pcs_per_box }})"
                                class="text-blue-600 hover:underline text-xs mr-2">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('master-barang.destroy', $row->id) }}" class="inline"
                                onsubmit="return confirm('Yakin hapus {{ $row->nama }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($barang->hasPages())
        <div class="px-6 py-4 border-t">{{ $barang->links() }}</div>
    @endif
</div>

<dialog id="modalEdit" class="rounded-lg shadow-2xl backdrop:bg-black/50 p-0 w-full max-w-md">
    <form method="POST" id="formEdit" class="p-6">
        @csrf @method('PUT')
        <h3 class="text-lg font-semibold mb-4">Edit Barang</h3>

        <div class="space-y-3">
            <div>
                <label class="block text-sm mb-1">Nama Barang</label>
                <input type="text" name="nama" id="editNama" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm mb-1">Box</label>
                    <input type="number" value="1" readonly class="w-full border rounded px-3 py-2 text-sm bg-gray-100 text-center">
                </div>
                <div>
                    <label class="block text-sm mb-1">Isi per Box (Pcs)</label>
                    <input type="number" name="pcs_per_box" id="editPcsPerBox" min="1" required class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div class="flex gap-2 mt-5">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium">Simpan</button>
            <button type="button" onclick="document.getElementById('modalEdit').close()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded text-sm">Batal</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    function bukaEdit(id, nama, pcsPerBox) {
        document.getElementById('formEdit').action = `/master-barang/${id}`;
        document.getElementById('editNama').value = nama;
        document.getElementById('editPcsPerBox').value = pcsPerBox;
        document.getElementById('modalEdit').showModal();
    }
</script>

<script>
    const qtyBoxInput = document.getElementById('inputQtyBox');
    const pcsPerBoxInput = document.getElementById('inputPcsPerBox');
    const totalPcsInput = document.getElementById('inputTotalPcs');

    function hitungTotalPcs() {
        const box = parseInt(qtyBoxInput.value) || 0;
        const pcs = parseInt(pcsPerBoxInput.value) || 0;
        totalPcsInput.value = box * pcs;
    }

    qtyBoxInput.addEventListener('input', hitungTotalPcs);
    pcsPerBoxInput.addEventListener('input', hitungTotalPcs);
    hitungTotalPcs(); // Jalankan saat halaman dimuat
</script>
@endpush
@endsection