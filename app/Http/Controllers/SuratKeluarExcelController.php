<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SuratKeluarExport;
use App\Imports\SuratKeluarImport;
use Illuminate\Support\Facades\Log;

class SuratKeluarExcelController extends Controller
{
    public function export(Request $request)
    {
        return Excel::download(new SuratKeluarExport($request->all()), 'surat_keluar_' . date('Ymd_His') . '.xlsx');
    }

    public function exportDibDb(Request $request)
    {
        $ids = null;
        if ($request->has('ids') && !empty($request->input('ids'))) {
            $rawIds = $request->input('ids');
            $ids = is_array($rawIds) ? $rawIds : explode(',', $rawIds);
            $ids = array_filter(array_map('trim', $ids));
        }

        $filters = $request->only([
            'search',
            'tanggal_surat_dari',
            'tanggal_surat_sampai',
        ]);

        $tahun = $request->input('tahun');

        $exporter = new \App\Exports\SuratKeluarDibDbExport($ids, $filters, $tahun);
        $spreadsheet = $exporter->createSpreadsheet();

        $filename = 'arsip_DIB_DB_surat_keluar_' . date('Y-m-d_H-i-s') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            set_time_limit(0);
            $file = $request->file('file');
            
            // Allow reading of properties (hyperlinks) by not making it read_only
            config(['excel.imports.read_only' => false]);
            
            Excel::import(new SuratKeluarImport, $file);

            return back()->with('success', 'Data Surat Keluar berhasil diimpor!');
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}
