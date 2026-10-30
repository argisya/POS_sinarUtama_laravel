@extends('layouts.app')
@section('title', 'Barang Masuk')

@section('content')

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
                        {{ $b->kode_barang }} — {{ $b->nama }}
                    </option>
                @endforeach
            </select>
            @error('barang_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Jumlah Box <span class="text-red-500">*</span></label>
            <input type="number" name="jumlah_box" id="inputBox" min="1" value="{{ old('jumlah_box') }}" required
                class="w-full border rounded px-3 py-2 text-sm @error('jumlah_box') border-red-500 @enderror">
            @error('jumlah_box') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Total Pcs (Otomatis)</label>
            <input type="number" name="jumlah" id="inputPcs" readonly
                class="w-full border rounded px-3 py-2 text-sm bg-gray-100 cursor-not-allowed">
        </div>

        <div>
            <label class="block text-sm mb-1">Harga / Box (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="harga_satuan" min="1" value="{{ old('harga_satuan') }}" required
                placeholder="Modal per box"
                class="w-full border rounded px-3 py-2 text-sm @error('harga_satuan') border-red-500 @enderror">
            @error('harga_satuan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                class="w-full border rounded px-3 py-2 text-sm">
            @error('tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Jatuh Tempo</label>
            <input type="date" name="tanggal_jatuh_tempo" value="{{ old('tanggal_jatuh_tempo') }}"
                class="w-full border rounded px-3 py-2 text-sm @error('tanggal_jatuh_tempo') border-red-500 @enderror">
            <p class="text-[10px] text-gray-500 mt-1">Kosongkan jika langsung lunas</p>
            @error('tanggal_jatuh_tempo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Status Bayar <span class="text-red-500">*</span></label>
            <select name="status_pembayaran" required
                class="w-full border rounded px-3 py-2 text-sm @error('status_pembayaran') border-red-500 @enderror">
                <option value="lunas"        @selected(old('status_pembayaran') === 'lunas')>Lunas</option>
                <option value="belum_lunas"  @selected(old('status_pembayaran', 'belum_lunas') === 'belum_lunas')>Belum Lunas</option>
            </select>
            @error('status_pembayaran') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-4">
            <label class="block text-sm mb-1">Supplier</label>
            <input type="text" name="supplier" value="{{ old('supplier') }}"
                class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div class="md:col-span-6">
            <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded text-sm font-medium">
                Simpan
            </button>
            <span class="text-xs text-gray-500 ml-3">
                ℹ️ Harga satuan akan menjadi <b>modal terbaru</b> barang ini.
            </span>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b">
        <h3 class="font-semibold text-gray-700">Riwayat Barang Masuk</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Jatuh Tempo</th>
                    <th class="px-4 py-3 text-left">Barang</th>
                    <th class="px-4 py-3 text-right">Jumlah Box</th> 
                    <th class="px-4 py-3 text-right">Jumlah Pcs</th> 
                    <th class="px-4 py-3 text-right">Harga / Box</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-left">Supplier</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($barangMasuk as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $row->barang->kode_barang }}</td>
                        <td class="px-4 py-3">{{ $row->tanggal->format('d/m/Y') }}</td>
                        
                        {{-- Tanggal Jatuh Tempo --}}
                        <td class="px-4 py-3">
                            @if ($row->tanggal_jatuh_tempo)
                                @php
                                    $isOverdue = $row->status_pembayaran === 'belum_lunas' && $row->tanggal_jatuh_tempo->isPast();
                                @endphp
                                <span class="{{ $isOverdue ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                    {{ $row->tanggal_jatuh_tempo->format('d/m/Y') }}
                                    @if($isOverdue) ⚠️ @endif
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 font-medium">{{ $row->barang->nama }}</td>
                        
                        {{-- Jumlah Box --}}
                        <td class="px-4 py-3 text-right font-semibold text-gray-700">
                            {{ number_format($row->jumlah_box) }} Box
                        </td>

                        {{-- Jumlah Pcs --}}
                        <td class="px-4 py-3 text-right text-gray-600">
                            {{ number_format($row->jumlah) }} Pcs
                        </td>

                        <td class="px-4 py-3 text-right">Rp {{ number_format($row->harga_satuan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold">
                            Rp {{ number_format($row->jumlah_box * $row->harga_satuan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">{{ $row->supplier ?? '-' }}</td>
                        
                        {{-- Status --}}
                        <td class="px-4 py-3 text-center">
                            @if ($row->status_pembayaran === 'lunas')
                                <span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Lunas</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Belum Lunas</span>
                            @endif
                        </td>
                        
                        {{-- Aksi --}}
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <button type="button" 
                                onclick="bukaEdit({{ $row->id }}, {{ $row->barang_id }}, {{ $row->jumlah_box }}, {{ $row->harga_satuan }}, '{{ $row->tanggal->format('Y-m-d') }}', '{{ $row->tanggal_jatuh_tempo ? $row->tanggal_jatuh_tempo->format('Y-m-d') : '' }}', @js($row->supplier), '{{ $row->status_pembayaran }}')"
                                class="text-blue-600 hover:underline text-xs mr-2">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('barang-masuk.destroy', $row->id) }}" class="inline" onsubmit="return confirm('Yakin hapus? Stok akan dikurangi.')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-100 font-semibold text-sm">
                <tr>
                    <td colspan="7" class="px-4 py-3 text-right text-gray-700">Grand Total:</td>
                    <td class="px-4 py-3 text-right text-indigo-700">
                        Rp {{ number_format($grandTotal ?? 0, 0, ',', '.') }}
                    </td>
                    <td colspan="3" class="px-4 py-3"></td>
                </tr>
                <tr class="text-xs">
                    <td colspan="7" class="px-4 py-2 text-right text-gray-500">— Lunas:</td>
                    <td class="px-4 py-2 text-right text-emerald-600">
                        Rp {{ number_format($grandTotalLunas ?? 0, 0, ',', '.') }}
                    </td>
                    <td colspan="3" class="px-4 py-2"></td>
                </tr>
                <tr class="text-xs">
                    <td colspan="7" class="px-4 py-2 text-right text-gray-500">— Belum Lunas:</td>
                    <td class="px-4 py-2 text-right text-red-600">
                        Rp {{ number_format($grandTotalBelumLunas ?? 0, 0, ',', '.') }}
                    </td>
                    <td colspan="3" class="px-4 py-2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if ($barangMasuk->hasPages())
        <div class="px-6 py-4 border-t">{{ $barangMasuk->links() }}</div>
    @endif
</div>
<dialog id="modalEdit" class="rounded-lg shadow-2xl backdrop:bg-black/50 p-0 w-full max-w-2xl">
    <form method="POST" id="formEdit" class="p-6">
        @csrf @method('PUT')
        <h3 class="text-lg font-semibold mb-4">Edit Barang Masuk</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Barang</label>
                <select name="barang_id" id="editBarangId" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach ($barang as $b)
                        <option value="{{ $b->id }}">{{ $b->kode_barang }} — {{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Jumlah Box</label>
                <input type="number" name="jumlah_box" id="editJumlahBox" min="1" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm mb-1">Harga / Box (Rp)</label>
                <input type="number" name="harga_satuan" id="editHarga" min="1" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal</label>
                <input type="date" name="tanggal" id="editTanggal" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm mb-1">Jatuh Tempo</label>
                <input type="date" name="tanggal_jatuh_tempo" id="editJatuhTempo" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm mb-1">Supplier</label>
                <input type="text" name="supplier" id="editSupplier" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm mb-1">Status</label>
                <select name="status_pembayaran" id="editStatus" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="lunas">Lunas</option>
                    <option value="belum_lunas">Belum Lunas</option>
                </select>
            </div>
        </div>

        <div class="flex gap-2 mt-6">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded text-sm font-medium">Simpan Perubahan</button>
            <button type="button" onclick="document.getElementById('modalEdit').close()" class="bg-gray-200 hover:bg-gray-300 px-5 py-2 rounded text-sm">Batal</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    const dataBarang = @json($barang);
    const selectBarang = document.querySelector('select[name="barang_id"]');
    const inputBox = document.getElementById('inputBox');
    const inputPcs = document.getElementById('inputPcs');

    function hitungPcs() {
        const id = selectBarang.value;
        const box = parseInt(inputBox.value) || 0;
        const barang = dataBarang.find(b => b.id == id);

        if (barang) {
            inputPcs.value = box * barang.pcs_per_box;
        } else {
            inputPcs.value = 0;
        }
    }

    selectBarang.addEventListener('change', hitungPcs);
    inputBox.addEventListener('input', hitungPcs);
</script>

<script>
    function bukaEdit(id, barangId, box, harga, tgl, jatuhTempo, supplier, status) {
        document.getElementById('formEdit').action = `/barang-masuk/${id}`;
        document.getElementById('editBarangId').value = barangId;
        document.getElementById('editJumlahBox').value = box;
        document.getElementById('editHarga').value = harga;
        document.getElementById('editTanggal').value = tgl;
        document.getElementById('editJatuhTempo').value = jatuhTempo;
        document.getElementById('editSupplier').value = supplier || '';
        document.getElementById('editStatus').value = status;
        document.getElementById('modalEdit').showModal();
    }
</script>
@endpush

@endsection