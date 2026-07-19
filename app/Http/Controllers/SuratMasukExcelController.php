<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SuratMasukExport;
use App\Imports\SuratMasukImport;

class SuratMasukExcelController extends Controller
{
    public function export()
    {
        return Excel::download(new SuratMasukExport, 'surat_masuk_' . date('Y-m-d_H-i-s') . '.xlsx');
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
