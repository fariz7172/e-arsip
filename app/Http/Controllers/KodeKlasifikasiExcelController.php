<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KodeKlasifikasiExport;
use App\Exports\KodeKlasifikasiTemplateExport;
use App\Imports\KodeKlasifikasiImport;

class KodeKlasifikasiExcelController extends Controller
{
    public function export(Request $request)
    {
        $search = $request->query('search');
        return Excel::download(new KodeKlasifikasiExport($search), 'kode_klasifikasi_' . date('Ymd_His') . '.xlsx');
    }

    public function template()
    {
        return Excel::download(new KodeKlasifikasiTemplateExport, 'template_kode_klasifikasi.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            set_time_limit(0);
            config(['excel.imports.read_only' => false]);

            $import = new KodeKlasifikasiImport();
            Excel::import($import, $request->file('file'));

            return redirect()->back()->with('success', 'Data Kode Klasifikasi berhasil diimpor!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
}

