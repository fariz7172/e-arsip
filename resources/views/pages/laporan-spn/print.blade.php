<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan SPN - {{ $payment->no_spm ?? 'Dokumen' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Modern UI styling for web view */
        body { background: #f1f5f9; }
        .print-toolbar {
            position: fixed; top: 0; left: 0; right: 0; height: 60px;
            background: white; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; z-index: 50; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .print-container {
            margin-top: 80px; margin-bottom: 40px;
            display: flex; flex-direction: column; align-items: center; gap: 24px;
        }
        
        /* A4 Page styling */
        .print-area {
            background: white; width: 210mm; min-height: 297mm;
            padding: 15mm; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            position: relative; overflow: hidden;
        }

        /* Input overrides for print view */
        [contenteditable="true"]:hover {
            background: rgba(255, 255, 0, 0.2);
            outline: 1px dashed #cbd5e1;
            cursor: text;
        }
        [contenteditable="true"]:focus {
            background: rgba(255, 255, 0, 0.3);
            outline: 1px solid #3b82f6;
        }

        /* Print CSS */
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .print-toolbar { display: none !important; }
            .print-container { margin: 0; padding: 0; display: block; gap: 0; }
            .print-area {
                box-shadow: none; margin: 0; padding: 0; border: none;
                width: 210mm !important; min-height: 297mm !important;
                height: auto !important; overflow: visible !important;
            }
            @page { size: A4; margin: 15mm; }
            .print-area ~ .print-area { page-break-before: always; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            [contenteditable="true"] { outline: none !important; background: transparent !important; }
        }
    </style>
</head>
<body class="antialiased text-slate-800" x-data="spnComponent()">

    <!-- Print Toolbar -->
    <div class="print-toolbar">
        <div class="flex items-center gap-4">
            <button onclick="window.close()" class="flex items-center gap-2 text-slate-600 hover:text-slate-900 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                <span class="font-medium">Kembali</span>
            </button>
            <div class="h-6 w-[1px] bg-slate-300"></div>
            <div>
                <h1 class="font-bold text-slate-800 leading-tight">Laporan SPN (Daftar Isi Berkas)</h1>
                <p class="text-xs text-slate-500 font-medium">Payment ID: {{ $payment->id }} | No. SPM: {{ $payment->no_spm ?? '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button @click="saveData()" class="flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-md font-medium transition-colors shadow-sm" :class="{ 'opacity-75 cursor-wait': isSaving }">
                <template x-if="!isSaving"><i data-lucide="save" class="w-4 h-4"></i></template>
                <template x-if="isSaving">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </template>
                <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Data'"></span>
            </button>
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium transition-colors shadow-sm">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak A4</span>
            </button>
        </div>
    </div>

    <!-- Print Canvas -->
    <div class="print-container relative">

        @if($payment->bundle)
            <!-- Bundle Link Banner (Floating on right side for desktop) -->
            <div class="print:hidden fixed top-24 right-8 z-40 bg-white/95 backdrop-blur border border-indigo-100 shadow-xl rounded-xl p-4 w-72 transition-all hover:shadow-2xl">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                        <i data-lucide="folder-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-800 leading-tight">Bundle Fisik Terhubung</h3>
                        <p class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $payment->bundle->kode ?? 'Bundle #'.$payment->bundle->id }}</p>
                    </div>
                </div>
                <a href="/bundles/{{ $payment->bundle->id }}/detail" target="_blank" class="block w-full text-center bg-indigo-600 text-white text-xs font-bold py-2.5 rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    Buka Bundle di Tab Baru ↗
                </a>
                <p class="text-[10px] text-slate-400 mt-2 text-center leading-tight">Buka bundle untuk memastikan kelengkapan data saat mencentang checklist.</p>
            </div>
        @endif

        <!-- PAGE: LAPORAN SPN (DAFTAR ISI BERKAS) -->
        <div class="print-area font-serif page-no-override" id="spn-content">
            <!-- Kop Surat -->
            <div class="flex items-center border-b-[3px] border-black pb-2 mb-6 text-center relative">
                <div class="w-[110px] pr-4"><img src="{{ asset('assets/logo.png') }}" class="w-full"></div>
                <div class="flex-1 text-center">
                    <h1 class="text-[12pt] font-bold leading-tight uppercase">PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA</h1>
                    <h2 class="text-[14pt] font-bold leading-tight uppercase">DINAS SUMBER DAYA AIR</h2>
                    <h3 class="text-[12pt] font-bold leading-tight uppercase">SUKU DINAS SUMBER DAYA AIR KOTA ADMINISTRASI JAKARTA UTARA</h3>
                    <p class="text-[9pt] leading-tight mt-1 font-sans">Jl. Yos Sudarso No. 27- 29 Telp. / Fax 43902028 Email: Sudinsdaju@gmail.com <br>Jakarta</p>
                </div>
                <div class="absolute bottom-2 right-0 text-[9pt] font-sans">Kode Pos: 14320</div>
            </div>
            
            <div class="text-center mb-6 relative group">
                <h1 class="text-[12pt] font-bold uppercase underline">DAFTAR ISI BERKAS</h1>
                <!-- Check All Button (Hidden on print) -->
                <button @click="let allChecked = checklistSPN.every(i => i.is_uploaded); checklistSPN.forEach(i => i.is_uploaded = !allChecked); savePrint()" class="print:hidden absolute right-0 top-1/2 -translate-y-1/2 text-[10px] px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded font-sans opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                    <i data-lucide="check-square" class="w-3 h-3"></i> Tandai Semua
                </button>
            </div>

            <div class="grid grid-cols-[160px_10px_1fr] gap-y-1 mb-4 text-[10pt] leading-tight">
                <span class="">UNIT PENGOLAH</span><span>:</span><span contenteditable="true" data-eid="spn-1" class="uppercase">Suku Dinas Sumber Daya Air Kota Administrasi Jakarta Utara</span>
                <span class="">Nomor SPM</span><span>:</span><span contenteditable="true" data-eid="spn-2" class="">{{ $payment->no_spm }}</span>
            </div>

            <!-- Table -->
            <table class="w-full border-collapse border-[1.5px] border-black text-[8.5pt]">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="border border-black px-2 py-2 w-12 text-center">Nomor Berkas</th>
                        <th class="border border-black px-2 py-2 w-12 text-center">Nomor Item Arsip</th>
                        <th class="border border-black px-2 py-2 w-24 text-center">Kode Klasifikasi</th>
                        <th class="border border-black px-2 py-2 text-center">Uraian Informasi Arsip</th>
                        <th class="border border-black px-2 py-2 w-20 text-center">Tanggal</th>
                        <th class="border border-black px-2 py-2 w-16 text-center">Jumlah</th>
                        <th class="border border-black px-2 py-2 w-20 text-center">Keterangan</th>
                        <th class="border border-black px-2 py-2 w-14 text-center print:hidden" title="Status Kelengkapan Data">Status</th>
                        <th class="border border-black px-2 py-2 w-8 text-center print:hidden" title="Aksi Hapus">🗑️</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in checklistSPN" :key="index">
                        <tr class="group transition-colors" :class="item.is_uploaded ? 'bg-emerald-50/60' : ''">
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.no_berkas" @blur="item.no_berkas = $el.innerText; savePrint()"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.no_item" @blur="item.no_item = $el.innerText; savePrint()"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.kode" @blur="item.kode = $el.innerText; savePrint()"></td>
                            <td class="border border-black px-2 py-1 leading-tight outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.uraian" @blur="item.uraian = $el.innerText; savePrint()"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.tanggal" @blur="item.tanggal = $el.innerText; savePrint()"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.jumlah" @blur="item.jumlah = $el.innerText; savePrint()"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.keterangan" @blur="item.keterangan = $el.innerText; savePrint()"></td>
                            
                            <!-- Checkbox Kelengkapan (Hanya Tampil di Web) -->
                            <td class="border border-black px-1 py-1 text-center print:hidden align-middle">
                                <label class="flex items-center justify-center w-full h-full cursor-pointer">
                                    <input type="checkbox" x-model="item.is_uploaded" @change="savePrint()" class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 cursor-pointer">
                                </label>
                            </td>

                            <!-- Aksi Hapus (Hanya Tampil di Web) -->
                            <td class="border border-black px-1 py-1 text-center print:hidden align-middle">
                                <button @click="checklistSPN.splice(index, 1); savePrint()" class="text-red-400 hover:text-red-600 transition-colors opacity-0 group-hover:opacity-100 mx-auto" title="Hapus Baris">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            
            <div class="mt-2 text-center print:hidden">
                <button @click="checklistSPN.push({ no_berkas:'', no_item: checklistSPN.length + 1, kode:'', uraian:'', tanggal:'', jumlah:'1', keterangan:'Berkas', is_uploaded: false }); savePrint()" class="bg-blue-50 text-blue-600 border border-blue-200 px-4 py-1.5 rounded text-xs font-semibold hover:bg-blue-100 transition-colors shadow-sm inline-flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Baris Arsip
                </button>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('spnComponent', () => ({
                isSaving: false,
                checklistSPN: @json($payment->print_data['checklistSPN'] ?? null) || [
                    { "no_berkas": "1", "no_item": "1", "kode": "-1.793.2", "uraian": "Surat Perintah Kerja kegiatan pengelolaan sda dan bangunan pengaman Pantai pada Wilayah Sungai Lintas Daerah Kabupaten/Kota ( {{ $payment->vendor?->nama_perusahaan ?? '...' }} )", "tanggal": "07/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "2", "kode": "-1.793.2", "uraian": "Surat Pesanan Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "07/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "3", "kode": "-1.793.2", "uraian": "Surat Perintah Kerja Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "07/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "4", "kode": "-1.793.2", "uraian": "Surat Perintah Mulai Kerja Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "07/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "5", "kode": "-1.793.2", "uraian": "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "06/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "6", "kode": "PPBJ/2022", "uraian": "Pemenang Lelang Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "06/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "7", "kode": "PPBJ/2022", "uraian": "Peyampaian Berita Acara Hasil Pengadaan Langsung Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "06/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "8", "kode": "PPBJ/2022", "uraian": "Penetapan penyediaan pengadaan langsung Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "06/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "9", "kode": "PPBJ/2022", "uraian": "Berita Acara Hasil Pengadaan Langsung Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "06/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "10", "kode": "PPBJ/2022", "uraian": "Berita Acara Klarifikasi Teknis dan Negosiasi Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "03/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "11", "kode": "PPBJ/2022", "uraian": "Daftar Hadir Klarifikasi Teknis dan Negosiasi Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "03/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "12", "kode": "", "uraian": "Harga Negosiasi", "tanggal": "03/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "13", "kode": "PPBJ/2022", "uraian": "Undangan  Klarifikasi Teknis dan Negosiasi Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "02/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "14", "kode": "PPBJ/2022", "uraian": "Berita Acara Hasil Evaluasi Pengadaan Pengadaan Barang Langsung Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "03/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "15", "kode": "PPBJ/2022", "uraian": "Lampiran Berita Acara Hasil Evaluasi Pengadaan Pengadaan Barang Langsung Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "03/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "16", "kode": "PPBJ/2022", "uraian": "Berita Acara Pembuktian Kualifikasi Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "03/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "17", "kode": "PPBJ/2022", "uraian": "Koreksi Aritmatika dan Evaluasi Prosentase Harga Penawaran Penyedia Barang/Jasa Terhadap Harga Perkiraan Sendiri (HPS)", "tanggal": "01/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "18", "kode": "PPBJ/2022", "uraian": "Hasil Pembukaan Surat Penawaran Harga Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "01/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "19", "kode": "PPBJ/2022", "uraian": "Berita Acara Pemasukan dan Pembukaan Surat Penawaran Harga Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "01/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "20", "kode": "", "uraian": "Daftar Hadir Pemasukan/Pembukaan Dokumen Penawaran (SPH)", "tanggal": "01/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "21", "kode": "PPBJ/2022", "uraian": "Berita Acara Pemberian Penjelasan (AANWIJING)", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "22", "kode": "", "uraian": "Daftar Hadir Penjelasan (AANWIJING)", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "23", "kode": "", "uraian": "Pakta Integritas Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "01/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "24", "kode": "PPBJ/2022", "uraian": "Undangan Pengadaan Langsung Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "28/02/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "25", "kode": "-1.793.2", "uraian": "Usulan Proses Pelaksanaan Permintaan Pengadaan Langsung", "tanggal": "27/02/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "26", "kode": "", "uraian": "Rencana Anggaran Biaya Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "27", "kode": "", "uraian": "Harga Perkiraan Sendiri Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "28", "kode": "", "uraian": "Bill Of Quantity (BOQ)", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "29", "kode": "PPBJ/2022", "uraian": "Nota Dinas", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "30", "kode": "", "uraian": "Kerangka Acuan Kerja", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "31", "kode": "MJT/SPH/II/JU/2022", "uraian": "Penawaran Harga", "tanggal": "01/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "32", "kode": "MJT/PPHP/II/JU/2022", "uraian": "Laporan Pekerjaan Selesai", "tanggal": "01/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "33", "kode": "MJT/PPHP/II/JU/2022", "uraian": "Permohonan Pemeriksaan Hasil Pekerjaan", "tanggal": "01/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "34", "kode": "-1.793.2", "uraian": "Pelaksanaan Pemeriksaan Hasil Pekerjaan", "tanggal": "06/03/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "35", "kode": "BAP-ET/03/2022", "uraian": "Berita Acara Pemeriksaan / Evaluasi Teknis", "tanggal": "10/04/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "36", "kode": "-1.793.2", "uraian": "Berita Acara Serah Terima Pekerjaan", "tanggal": "11/04/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "37", "kode": "-1.793.2", "uraian": "Berita Acara Pemeliharaan Barang", "tanggal": "11/04/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "38", "kode": "-1.793.2", "uraian": "Berita Acara Pembayaran", "tanggal": "12/04/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "39", "kode": "-1.793.2", "uraian": "Penerbitan SPP-LS", "tanggal": "12/04/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "40", "kode": "PPTK/2022", "uraian": "Permohonan Pemprosesan Tagihan Pihak Ketiga", "tanggal": "12/04/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "41", "kode": "", "uraian": "Foto Pekerjaan", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "42", "kode": "", "uraian": "Tanda Terima SPM", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "43", "kode": "SP2D/VI/2022", "uraian": "Surat Perintah Pencairan Dan ( SP2D )", "tanggal": "17/04/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "44", "kode": "", "uraian": "Check List ( SPM )", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "45", "kode": "", "uraian": "Check List ( SPP )", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "46", "kode": "SPM/10302201/VI/2022", "uraian": "Surat Perintah Membayar ( SPM )", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "47", "kode": "", "uraian": "Ringkasan Kontrak", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "48", "kode": "", "uraian": "Surat Pernyataan Tanggung Jawab - LS", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "49", "kode": "SPM/10302201/VI/2022", "uraian": "Surat Pernyataan Tanggung Jawab Pengajuan SPM-LS", "tanggal": "17/04/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "50", "kode": "SPP/10302201/VI/2022", "uraian": "Surat Permintaan Pembayaran ( SPP )", "tanggal": "17/04/23", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "51", "kode": "MJT/KWT/III/JU/2022", "uraian": "Kwitansi", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "52", "kode": "", "uraian": "Faktur Pajak", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "53", "kode": "", "uraian": "NPWP ( Fotocopy )", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "54", "kode": "", "uraian": "Rekening Koran", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "55", "kode": "", "uraian": "Company Profile", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" }
                ],
                
                // Helper to collect all contenteditable data just before save
                collectManualEdits() {
                    let savedContentData = @json($payment->print_data['savedContentData'] ?? new stdClass());
                    document.querySelectorAll('[data-eid]').forEach(el => {
                        savedContentData[el.getAttribute('data-eid')] = el.innerText;
                    });
                    return savedContentData;
                },

                async saveData() {
                    this.isSaving = true;
                    try {
                        let currentPrintData = @json($payment->print_data ?? []);
                        let savedContentData = this.collectManualEdits();
                        
                        let response = await fetch('{{ route('payments.save-print', $payment->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                print_data: {
                                    ...currentPrintData,
                                    savedContentData: savedContentData,
                                    checklistSPN: this.checklistSPN
                                }
                            })
                        });
                        let result = await response.json();
                        if(result.success) {
                            alert('Data Laporan SPN berhasil disimpan permanen!');
                        }
                    } catch(e) {
                        alert('Gagal menyimpan data.');
                    } finally {
                        this.isSaving = false;
                    }
                },
                init() {
                    // Populate saved contenteditables on load
                    let savedContentData = @json($payment->print_data['savedContentData'] ?? new stdClass());
                    document.querySelectorAll('[data-eid]').forEach(el => {
                        let eid = el.getAttribute('data-eid');
                        if (savedContentData[eid] !== undefined) {
                            el.innerText = savedContentData[eid];
                        }
                    });
                }
            }));
        });
        
        lucide.createIcons();
    </script>
</body>
</html>
