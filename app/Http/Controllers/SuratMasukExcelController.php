<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SuratMasukExport;
use App\Exports\SuratMasukArsipExport;
use App\Imports\SuratMasukImport;

class SuratMasukExcelController extends Controller
{
    public function export(Request $request)
    {
        return Excel::download(new SuratMasukExport($request->all()), 'surat_masuk_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function exportArsip(Request $request)
    {
        return Excel::download(new SuratMasukArsipExport($request->all()), 'arsip_surat_masuk_DIB_' . date('Y-m-d_H-i-s') . '.xlsx');
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
            'tanggal_acara_dari',
            'tanggal_acara_sampai',
        ]);

        $tahun = $request->input('tahun');

        $exporter = new \App\Exports\SuratMasukDibDbExport($ids, $filters, $tahun);
        $spreadsheet = $exporter->createSpreadsheet();

        $filename = 'arsip_DIB_DB_surat_masuk_' . date('Y-m-d_H-i-s') . '.xlsx';

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
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        try {
            // Menonaktifkan batas waktu eksekusi (menghindari timeout saat import data besar)
            set_time_limit(0);
            
            // Menonaktifkan read_only agar sistem bisa membaca tipe data khusus seperti Hyperlink (Google Drive)
            config(['excel.imports.read_only' => false]);
            Excel::import(new SuratMasukImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Surat Masuk berhasil diimpor!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}
