<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Surat Keluar</title>
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
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1.5px solid black; padding: 8px; text-align: center; vertical-align: top; }
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
        }
    </style>
</head>
<body class="antialiased text-slate-800">
    <div class="print-toolbar">
        <div class="flex items-center gap-4">
            <button onclick="window.close()" class="flex items-center gap-2 text-slate-600 hover:text-slate-900 font-medium transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </button>
            <div class="h-6 w-[1px] bg-slate-300"></div>
            <div>
                <h1 class="font-bold text-slate-800 leading-tight">Cetak Laporan Surat Keluar</h1>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium transition-colors shadow-sm">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak A4
            </button>
        </div>
    </div>

    <div class="print-container relative">
        <div class="print-area">
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

            <!-- TITLE -->
            <div class="text-center font-bold mb-6" style="font-size: 14pt; text-decoration: underline;">
                REKAPITULASI SURAT KELUAR
            </div>

            <!-- MAIN TABLE -->
            <table style="font-size: 11pt; width: 100%;">
                <thead class="font-bold bg-gray-50">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 120px;">Tanggal Surat</th>
                        <th style="width: 200px;">Nomor Surat</th>
                        <th>Tujuan Surat</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $no = 1; 
                    @endphp
                    @foreach($surats as $surat)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $surat->tanggal ? \Carbon\Carbon::parse($surat->tanggal)->format('d M Y') : '-' }}</td>
                            <td class="font-bold">{{ $surat->no_surat ?? '-' }}</td>
                            <td class="text-left">{{ $surat->tujuan_surat ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50">
                        <td colspan="3" style="text-align: right; padding-right: 15px;">TOTAL KESELURUHAN</td>
                        <td>{{ count($surats) }} Data</td>
                    </tr>
                </tfoot>
            </table>

            @if(count($duplicates) > 0)
            <div class="mt-6 text-sm" style="font-size: 11pt;">
                <p class="font-bold mb-2">Data dengan Nomor Surat yang Sama (Duplikat):</p>
                <ul style="list-style-type: none; padding-left: 0; margin-top: 0;">
                    @foreach($duplicates as $no_surat => $count)
                        <li class="mb-1"><span class="font-bold">{{ $no_surat ?: '(Tanpa Nomor)' }}</span> = {{ $count }} data</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
