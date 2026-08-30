<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratKeluar;

class SuratKeluarController extends Controller
{
    /**
     * Show the batch Surat Keluar print view (grouped by no_surat).
     */
    public function printBatch(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));
        $ids = array_filter(array_map('trim', $ids));
        
        if (empty($ids)) {
            // Ambil semua data jika tidak ada ID yang di-pass
            $surats = SuratKeluar::orderBy('tanggal', 'desc')->orderByRaw('CAST(no_urut AS UNSIGNED) DESC')->get();
        } else {
            // Ambil berdasarkan ID yang dipilih
            $surats = SuratKeluar::whereIn('id', $ids)->get();
        }

        if ($surats->isEmpty()) {
            return back()->with('error', 'Data surat keluar tidak ditemukan.');
        }

        // Hitung no_surat yang sama (duplikat)
        $duplicates = $surats->groupBy(function($item) {
            return trim($item->no_surat);
        })->map->count()->filter(function($count) {
            return $count > 1; // Hanya ambil yang jumlahnya lebih dari 1
        });

        return view('pages.surat-keluar.print-batch', compact('surats', 'duplicates'));
    }
}
