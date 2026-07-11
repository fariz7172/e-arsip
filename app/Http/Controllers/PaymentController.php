<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    /**
     * Fetch payment from external API and sync to local DB.
     */
    public function sync($id)
    {
        try {
            $response = Http::get("https://aplikasimailingsudin.farizahmad.com/api/payments/{$id}");
            
            if ($response->successful()) {
                $data = $response->json('data');
                
                if (!$data) {
                    return back()->with('error', 'Data tidak ditemukan di API.');
                }
                
                // Remove timestamps if they conflict, though updateOrCreate handles them
                unset($data['created_at'], $data['updated_at']);
                
                $payment = Payment::updateOrCreate(
                    ['id' => $data['id']],
                    $data
                );
                
                return redirect()->route('payments.index')
                                 ->with('success', 'Data berhasil disinkronisasi.');
            }
            
            return back()->with('error', 'Gagal mengambil data dari API. Status: ' . $response->status());
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Fetch multiple payments by date range from external API and sync to local DB.
     */
    public function syncBatch(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $apiUrl = env('MAILING_API_URL', 'https://aplikasimailingsudin.farizahmad.com/api');
            $response = Http::get("{$apiUrl}/payments", [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
            
            if ($response->successful()) {
                $dataList = $response->json('data');
                
                if (empty($dataList)) {
                    return back()->with('error', 'Tidak ada data pada rentang tanggal tersebut.');
                }
                
                $count = 0;
                foreach ($dataList as $data) {
                    // Fallback filter lokal: filter murni berdasarkan created_at
                    $createdAt = !empty($data['created_at']) ? substr($data['created_at'], 0, 10) : null;
                    
                    if ($createdAt >= $request->start_date && $createdAt <= $request->end_date) {
                        
                        unset($data['created_at'], $data['updated_at']);
                        Payment::updateOrCreate(
                            ['id' => $data['id']],
                            $data
                        );
                        $count++;
                    }
                }
                
                if ($count === 0) {
                    return back()->with('error', 'Semua data diabaikan karena tidak ada yang sesuai rentang Tanggal Dibuat (created_at).');
                }
                
                return redirect()->route('payments.index')
                                 ->with('success', "Berhasil menarik $count data pembayaran.");
            }
            
            return back()->with('error', 'Gagal mengambil data dari API. Status: ' . $response->status());
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the print view.
     */
    public function print(Payment $payment)
    {
        return view('print', compact('payment'));
    }

    /**
     * Show the SPN print view.
     */
    public function printSpn(Payment $payment)
    {
        return view('pages.laporan-spn.print', compact('payment'));
    }

    /**
     * Save print layout data (checklists, text inputs).
     */
    public function savePrint(Request $request, Payment $payment)
    {
        $request->validate([
            'print_data' => 'nullable|array'
        ]);

        $payment->update([
            'print_data' => $request->print_data
        ]);

        return response()->json(['success' => true, 'message' => 'Data cetak berhasil disimpan.']);
    }
}
