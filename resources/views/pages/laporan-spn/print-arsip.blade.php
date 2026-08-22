<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Daftar Arsip</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
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
        .print-area {
            background: white; min-width: 210mm; min-height: 297mm;
            padding: 15mm; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            position: relative; overflow: visible; font-family: 'Times New Roman', Times, serif;
        }
        [contenteditable="true"]:hover {
            background: rgba(255, 255, 0, 0.2); outline: 1px dashed #cbd5e1; cursor: text;
        }
        [contenteditable="true"]:focus {
            background: rgba(255, 255, 0, 0.3); outline: 1px solid #3b82f6;
        }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1.5px solid black; padding: 6px; text-align: center; vertical-align: top; }
        .text-left { text-align: left; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .print-toolbar { display: none !important; }
            .print-container { margin: 0; padding: 0; display: block; gap: 0; }
            .print-area { box-shadow: none; margin: 0; padding: 0; border: none; width: 210mm !important; min-height: 297mm !important; height: auto !important; overflow: visible !important; }
            @page { size: A4; margin: 15mm; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            [contenteditable="true"] { outline: none !important; background: transparent !important; }
        }
    </style>
</head>
<body class="antialiased text-slate-800" x-data="arsipComponent()">
    <div class="print-toolbar">
        <div class="flex items-center gap-4">
            <button onclick="window.close()" class="flex items-center gap-2 text-slate-600 hover:text-slate-900 font-medium transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </button>
            <div class="h-6 w-[1px] bg-slate-300"></div>
            <div>
                <h1 class="font-bold text-slate-800 leading-tight">Daftar Arsip</h1>
                <p class="text-xs text-slate-500 font-medium">{{ count($payments) }} Dokumen Terpilih</p>
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
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak A4
            </button>
        </div>
    </div>

    <div class="print-container relative">
        <div class="print-area">
            <!-- KOP SURAT V2 -->
            <div class="flex items-center border-b-[3px] border-black pb-4 mb-6 relative">
                <div class="w-[100px] pr-4 flex-shrink-0">
                    <img src="{{ asset('assets/logo.png') }}" class="w-full">
                </div>
                <div class="flex-1 text-center font-bold">
                    <div style="font-size: 14pt;">PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA</div>
                    <div style="font-size: 16pt;">SUKU DINAS SUMBER DAYA AIR</div>
                    <div style="font-size: 14pt;">KOTA ADMINISTRASI JAKARTA UTARA</div>
                    <div style="font-size: 10pt; font-weight: normal; margin-top: 4px;">Jl. Yos Sudarso No 27 - 29 Telp. 021 43902028 Fax 021 43902028 Email : sudinsdaju@gmail.com</div>
                    <div class="flex justify-between w-full px-8 mt-1" style="font-size: 10pt; font-weight: normal;">
                        <span>JAKARTA</span>
                        <span>Kode Pos 14230</span>
                    </div>
                </div>
            </div>

            <!-- TITLE -->
            <div class="text-center font-bold underline mb-6" style="font-size: 12pt;">
                DAFTAR ISI BERKAS
            </div>

            <!-- UNIT PENGOLAH -->
            <table class="mb-4" style="width: 100%; border: none; font-size: 11pt;">
                <tr>
                    <td style="width: 120px; text-align: left; border: none; padding: 2px;">Unit Pengolah</td>
                    <td style="width: 10px; text-align: center; border: none; padding: 2px;">:</td>
                    <td style="text-align: left; border: none; padding: 2px;">Subbagian Umum Suku Dinas Sumber Daya Air Kota Administrasi Jakarta Utara</td>
                </tr>
            </table>

            <!-- MAIN TABLE -->
            <table style="font-size: 10pt; width: 100%;">
                <thead class="font-bold bg-gray-50">
                    <tr>
                        <th style="width: 120px;">Klasifikasi Arsip</th>
                        <th>Judul Naskah</th>
                        <th style="width: 80px;">Tahun</th>
                        <th style="width: 70px;">Jumlah</th>
                        <th style="width: 100px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        @php
                            $tahun = collect(explode('/', $payment->no_spm))->last();
                            if(!is_numeric($tahun)) {
                                $tahun = $payment->tgl_spm ? \Carbon\Carbon::parse($payment->tgl_spm)->format('Y') : date('Y');
                            }
                            
                            $jumlah = count($payment->print_data['checklistSPN'] ?? []);
                            if ($jumlah == 0) $jumlah = 34; // default

                            $row = $payment->print_data['arsip_row'] ?? [];
                            $valKlasifikasi = $row['klasifikasi'] ?? '-1.793.2';
                            $valJudul = $row['judul'] ?? (($payment->vendor?->nama_perusahaan ?? '-') . ' (' . ($payment->keperluan ?? '-') . ')');
                            $valTahun = $row['tahun'] ?? $tahun;
                            $valJumlah = $row['jumlah'] ?? $jumlah;
                            $valKeterangan = $row['keterangan'] ?? 'Berkas';
                        @endphp
                        <tr class="arsip-row" data-id="{{ $payment->id }}">
                            <td contenteditable="true" data-field="klasifikasi">{{ $valKlasifikasi }}</td>
                            <td class="text-left" contenteditable="true" data-field="judul">{{ $valJudul }}</td>
                            <td contenteditable="true" data-field="tahun">{{ $valTahun }}</td>
                            <td contenteditable="true" data-field="jumlah">{{ $valJumlah }}</td>
                            <td contenteditable="true" data-field="keterangan">{{ $valKeterangan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- SIGNATURE -->
            <div class="mt-12 flex justify-end" style="font-size: 11pt;">
                <div class="text-center" style="width: 300px;">
                    <div>Mengetahui,</div>
                    <div contenteditable="true" id="sig_jabatan1">{{ $signature['jabatan1'] ?? 'Kasubbag Tata Usaha' }}</div>
                    <div contenteditable="true" id="sig_instansi1">{{ $signature['instansi1'] ?? 'Suku Dinas Sumber Daya Air' }}</div>
                    <div contenteditable="true" id="sig_instansi2">{{ $signature['instansi2'] ?? 'Kota Administrasi Jakarta Utara' }}</div>
                    <div style="height: 80px;"></div>
                    <div class="font-bold underline" contenteditable="true" id="sig_nama">{{ $signature['nama'] ?? 'Boris Karlop Lumbangaol, ST, MT.' }}</div>
                    <div contenteditable="true" id="sig_nip">{{ $signature['nip'] ?? 'NIP. 197811062010011020' }}</div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('arsipComponent', () => ({
                isSaving: false,
                async saveData() {
                    this.isSaving = true;
                    
                    let signature = {
                        jabatan1: document.getElementById('sig_jabatan1').innerText,
                        instansi1: document.getElementById('sig_instansi1').innerText,
                        instansi2: document.getElementById('sig_instansi2').innerText,
                        nama: document.getElementById('sig_nama').innerText,
                        nip: document.getElementById('sig_nip').innerText,
                    };

                    let rows = {};
                    document.querySelectorAll('.arsip-row').forEach(tr => {
                        let id = tr.getAttribute('data-id');
                        rows[id] = {
                            klasifikasi: tr.querySelector('[data-field="klasifikasi"]').innerText,
                            judul: tr.querySelector('[data-field="judul"]').innerText,
                            tahun: tr.querySelector('[data-field="tahun"]').innerText,
                            jumlah: tr.querySelector('[data-field="jumlah"]').innerText,
                            keterangan: tr.querySelector('[data-field="keterangan"]').innerText,
                        };
                    });

                    try {
                        let response = await fetch('{{ route('laporan-spn.print-arsip.save') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ signature, rows })
                        });
                        let result = await response.json();
                        if(result.success) {
                            alert(result.message);
                        }
                    } catch(e) {
                        alert('Gagal menyimpan data.');
                    } finally {
                        this.isSaving = false;
                    }
                }
            }));
        });
        lucide.createIcons();
    </script>
</body>
</html>
