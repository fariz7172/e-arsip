<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SuratKeluarExport;
use App\Imports\SuratKeluarImport;
use Illuminate\Support\Facades\Log;

class SuratKeluarExcelController extends Controller
{
    public function export()
    {
        return Excel::download(new SuratKeluarExport, 'surat_keluar_' . date('Ymd_His') . '.xlsx');
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
