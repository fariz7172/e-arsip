<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal Laporan SPN</title>
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
            display: flex; flex-direction: column; align-items: stretch; width: max-content; margin-left: auto; margin-right: auto; gap: 24px;
        }
        
        /* A4 Page styling */
        .print-area {
            background: white; min-width: 210mm; min-height: 297mm;
            padding: 15mm; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            position: relative; overflow: visible;
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
            tfoot { display: table-row-group; }
            [contenteditable="true"] { outline: none !important; background: transparent !important; }
        }
    </style>
</head>
<body class="antialiased text-slate-800">

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
                <p class="text-xs text-slate-500 font-medium">Batch Print ({{ count($payments) }} Dokumen)</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <!-- Tombol Simpan disembunyikan pada Batch Print -->
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium transition-colors shadow-sm">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak A4</span>
            </button>
        </div>
    </div>

    <!-- Print Canvas -->
    <div class="print-container relative">
        @foreach($payments as $payment)
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
        <div class="print-area font-serif page-no-override" id="spn-content-{{ $payment->id }}" x-data="spnComponent{{ $payment->id }}()">
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
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.no_berkas" @blur="item.no_berkas = $el.innerText"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.no_item" @blur="item.no_item = $el.innerText"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.kode" @blur="item.kode = $el.innerText"></td>
                            <td class="border border-black px-2 py-1 leading-tight outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.uraian" @blur="item.uraian = $el.innerText"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.tanggal" @blur="item.tanggal = $el.innerText"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.jumlah" @blur="item.jumlah = $el.innerText"></td>
                            <td class="border border-black px-2 py-1 text-center outline-none focus:bg-yellow-50" contenteditable="true" x-text="item.keterangan" @blur="item.keterangan = $el.innerText"></td>
                            
                            <!-- Checkbox Kelengkapan (Hanya Tampil di Web) -->
                            <td class="border border-black px-1 py-1 text-center print:hidden align-middle">
                                <label class="flex items-center justify-center w-full h-full cursor-pointer">
                                    <input type="checkbox" x-model="item.is_uploaded" class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 cursor-pointer">
                                </label>
                            </td>

                            <!-- Aksi Hapus (Hanya Tampil di Web) -->
                            <td class="border border-black px-1 py-1 text-center print:hidden align-middle">
                                <button @click="checklistSPN.splice(index, 1)" class="text-red-400 hover:text-red-600 transition-colors opacity-0 group-hover:opacity-100 mx-auto" title="Hapus Baris">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold">
                        <td colspan="5" class="border border-black px-2 py-2 text-right uppercase">Total Jumlah</td>
                        <td class="border border-black px-2 py-2 text-center" x-text="checklistSPN.reduce((total, item) => total + (parseInt(item.jumlah) || 0), 0)"></td>
                        <td class="border border-black px-2 py-2 text-center">Berkas</td>
                        <td colspan="2" class="border border-black px-2 py-2 print:hidden"></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="mt-2 text-center print:hidden">
                <button @click="checklistSPN.push({ no_berkas:'', no_item: checklistSPN.length + 1, kode:'', uraian:'', tanggal:'', jumlah:'1', keterangan:'Berkas', is_uploaded: false }); savePrint()" class="bg-blue-50 text-blue-600 border border-blue-200 px-4 py-1.5 rounded text-xs font-semibold hover:bg-blue-100 transition-colors shadow-sm inline-flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Baris Arsip
                </button>
            </div>
        </div>
        @endforeach

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            @foreach($payments as $payment)
            Alpine.data('spnComponent{{ $payment->id }}', () => ({
                checklistSPN: @json($payment->print_data['checklistSPN'] ?? null) || [
                    { "no_berkas": "1", "no_item": "1", "kode": "/ PN.01.02", "uraian": "Surat Perintah Kerja kegiatan pengelolaan sda dan bangunan pengaman Pantai pada Wilayah Sungai Lintas Daerah Kabupaten/Kota ( {{ $payment->vendor?->nama_perusahaan ?? '...' }} )", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "2", "kode": "/ PN.01.02", "uraian": "Surat Pesanan Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "3", "kode": "/ PN.01.02", "uraian": "Surat Perintah Kerja Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "4", "kode": "/ PN.01.02", "uraian": "Surat Penunjukan Penyedia Barang/Jasa (SPPBJ) Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "5", "kode": " - ", "uraian": "Rencana Anggaran Biaya", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "6", "kode": " - ", "uraian": "Harga Perkiraan Sendiri", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "7", "kode": " - ", "uraian": "Kerangka Acuan Kerja", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "8", "kode": "BA / /VER/", "uraian": "Berita acara Verifikasi Persyaratan Aministrasi Penyedia Barang/Jasa Pada Katalog Elektronik Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "9", "kode": "/ PN.01.02", "uraian": "Surat Pernyataan Tanggung Jawab Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "10", "kode": "/ PN.01.02", "uraian": "Undangan Rapat Pemeriksaan Pekerjaan", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "11", "kode": "/BAP-ET/ /", "uraian": "Berita Acara Pemeriksaan Pekerjaan Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "12", "kode": "/ PN.01.02", "uraian": "Berita Acara Penyelesaian Pekerjaan {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "13", "kode": "/ PN.01.02", "uraian": "Berita Acara Serah Terima Pekerjaan Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "14", "kode": "/PPTK/", "uraian": "Berita Acara Hasil Evaluasi Pengadaan Pengadaan Barang Langsung Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "15", "kode": "/ PN.01.02", "uraian": "Berita Acara Penerimaan Barang/Aset Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "16", "kode": "/ PN.01.02", "uraian": "Berita Acara Pembayaran Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "17", "kode": "/ PN.01.02", "uraian": "Penerbitan SPP-LS Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "18", "kode": "/PPTK/", "uraian": "Permohonan Pemprosesan Tagihan Pihak Ketiga Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "19", "kode": "/SP2D/ /", "uraian": "Surat Perintah Pencairan Dana (SP2D) Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_sp2d ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "20", "kode": " - ", "uraian": "Checklist Persyaratan Penerbitan Surat Perintah Membayar (SPM)", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "21", "kode": " - ", "uraian": "Checklist Penerbitan Surat Permintaan Pembayaran (SPP)", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "22", "kode": "{{ $payment->no_spm ?? '...' }}", "uraian": "Surat Perintah Membayar (SPM)", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "23", "kode": " - ", "uraian": "Ringkasan Kontrak Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "24", "kode": "{{ $payment->no_spm ?? '...' }}", "uraian": "Surat Pernyataan Tanggung Jawab Mutlak UP/LS (Kasudin)", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "25", "kode": "/ PN.01.02", "uraian": "Surat Pernyataan Verifikasi PPK (Kasubbag) Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "26", "kode": "{{ $payment->no_spp ?? '...' }}", "uraian": "Surat Pernyataan Verifikasi PPTK Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spp ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "27", "kode": "{{ $payment->no_spm ?? '...' }}", "uraian": "Surat Pernyataan Pertanggung Jawaban Mutlak Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "28", "kode": "{{ $payment->no_spp ?? '...' }}", "uraian": "Surat Permintaan Pembayaran (SPP)  Pek. {{ $payment->keperluan ?? '...' }}", "tanggal": "{{ $payment->tgl_spp ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "29", "kode": " - ", "uraian": "Kwitansi", "tanggal": "{{ $payment->tgl_spm ?? '...' }}", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "30", "kode": " - ", "uraian": "Faktur Pajak", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "31", "kode": " - ", "uraian": "NPWP ( Fotocopy )", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "32", "kode": " - ", "uraian": "Rekening Koran ", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "33", "kode": " - ", "uraian": "Surat Pengantar Tagihan", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                    { "no_berkas": "", "no_item": "34", "kode": " - ", "uraian": "Company Profile", "tanggal": "", "jumlah": "1", "keterangan": "Berkas" },
                  ],
                
                init() {
                    // Populate saved contenteditables on load
                    let savedContentData = @json($payment->print_data['savedContentData'] ?? new stdClass());
                    let container = document.getElementById('spn-content-{{ $payment->id }}');
                    if (container) {
                        container.querySelectorAll('[data-eid]').forEach(el => {
                            let eid = el.getAttribute('data-eid');
                            if (savedContentData[eid] !== undefined) {
                                el.innerText = savedContentData[eid];
                            }
                        });
                    }
                }
            }));
            @endforeach
        });
        
        lucide.createIcons();
    </script>
</body>
</html>
