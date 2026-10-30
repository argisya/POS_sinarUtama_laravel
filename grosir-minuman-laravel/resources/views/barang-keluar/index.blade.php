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
                    @php
                        $stokBox = ($b->total_box_masuk ?? 0) - ($b->total_box_keluar ?? 0);
                    @endphp
                    <option value="{{ $b->id }}" @selected(old('barang_id') == $b->id)>
                        {{ $b->kode_barang }} — {{ $b->nama }} (Stok: {{ $stokBox }} Box)
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
            <label class="block text-sm mb-1">Harga Jual / Box (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="harga_jual" id="inputHargaJual" min="1" value="{{ old('harga_jual') }}" required
                placeholder="Harga jual per box"
                class="w-full border rounded px-3 py-2 text-sm @error('harga_jual') border-red-500 @enderror">
            @error('harga_jual') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Tambahan Input Total Otomatis --}}
        <div>
            <label class="block text-sm mb-1">Total (Otomatis)</label>
            <input type="text" id="inputTotalHarga" readonly
                class="w-full border rounded px-3 py-2 text-sm bg-gray-100 cursor-not-allowed font-bold text-emerald-700"
                value="Rp 0">
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
                    <th class="px-4 py-3 text-right">Jumlah</th> {{-- Gabungan Box & Pcs --}}
                    <th class="px-4 py-3 text-right">Harga Jual / Box</th>
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
                        
                        {{-- Tampilan Jumlah (Box & Pcs) --}}
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold">{{ number_format($row->jumlah_box) }} Box</span>
                            <span class="block text-[10px] text-gray-500">({{ number_format($row->jumlah) }} Pcs)</span>
                        </td>

                        {{-- Harga Jual per Box --}}
                        <td class="px-4 py-3 text-right">
                            Rp {{ number_format($row->harga_jual, 0, ',', '.') }}
                        </td>

                        {{-- Total --}}
                        <td class="px-4 py-3 text-right font-semibold text-emerald-700">
                            Rp {{ number_format($row->jumlah_box * $row->harga_jual, 0, ',', '.') }}
                        </td>
                        
                        <td class="px-4 py-3">{{ $row->pembeli ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($row->status_pembayaran === 'lunas')
                                <span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Lunas</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Belum Lunas</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <button type="button" 
                                onclick="bukaEdit({{ $row->id }}, {{ $row->barang_id }}, {{ $row->jumlah_box }}, {{ $row->harga_jual }}, '{{ $row->tanggal->format('Y-m-d') }}', @js($row->pembeli), '{{ $row->status_pembayaran }}', @js($row->keterangan))"
                                class="text-blue-600 hover:underline text-xs mr-2">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('barang-keluar.destroy', $row->id) }}" class="inline" onsubmit="return confirm('Yakin hapus? Stok akan dikembalikan.')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-100 font-semibold text-sm">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-right text-gray-700">Grand Total Pendapatan:</td>
                    <td class="px-4 py-3 text-right text-emerald-700">
                        Rp {{ number_format($grandTotal ?? 0, 0, ',', '.') }}
                    </td>
                    <td colspan="3" class="px-4 py-3"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if ($barangKeluar->hasPages())
        <div class="px-6 py-4 border-t">{{ $barangKeluar->links() }}</div>
    @endif
</div>

<dialog id="modalEdit" class="rounded-lg shadow-2xl backdrop:bg-black/50 p-0 w-full max-w-2xl">
    <form method="POST" id="formEdit" class="p-6">
        @csrf @method('PUT')
        <h3 class="text-lg font-semibold mb-4">Edit Barang Keluar</h3>

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
                <label class="block text-sm mb-1">Harga Jual / Box (Rp)</label>
                <input type="number" name="harga_jual" id="editHarga" min="1" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal</label>
                <input type="date" name="tanggal" id="editTanggal" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm mb-1">Pembeli</label>
                <input type="text" name="pembeli" id="editPembeli" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm mb-1">Status</label>
                <select name="status_pembayaran" id="editStatus" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="lunas">Lunas</option>
                    <option value="belum_lunas">Belum Lunas</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm mb-1">Keterangan</label>
                <input type="text" name="keterangan" id="editKeterangan" class="w-full border rounded px-3 py-2 text-sm">
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
    const stokMap = @json($barang->pluck('stok', 'id'));
    document.querySelector('select[name="barang_id"]').addEventListener('change', function () {
        const stok = stokMap[this.value];
        if (stok !== undefined) {
            document.querySelector('input[name="jumlah"]').max = stok;
        }
    });
</script>

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
    const inputJumlahBox = document.querySelector('input[name="jumlah_box"]');
    const inputHargaJual = document.getElementById('inputHargaJual');
    const inputTotalHarga = document.getElementById('inputTotalHarga');

    function hitungTotal() {
        const box = parseInt(inputJumlahBox.value) || 0;
        const harga = parseInt(inputHargaJual.value) || 0;
        const total = box * harga;
        
        inputTotalHarga.value = 'Rp ' + total.toLocaleString('id-ID');
    }

    inputJumlahBox.addEventListener('input', hitungTotal);
    inputHargaJual.addEventListener('input', hitungTotal);
</script>
<script>
    function bukaEdit(id, barangId, box, harga, tgl, pembeli, status, keterangan) {
        document.getElementById('formEdit').action = `/barang-keluar/${id}`;
        document.getElementById('editBarangId').value = barangId;
        document.getElementById('editJumlahBox').value = box;
        document.getElementById('editHarga').value = harga;
        document.getElementById('editTanggal').value = tgl;
        document.getElementById('editPembeli').value = pembeli || '';
        document.getElementById('editStatus').value = status;
        document.getElementById('editKeterangan').value = keterangan || '';
        document.getElementById('modalEdit').showModal();
    }
</script>
@endpush
@endsection