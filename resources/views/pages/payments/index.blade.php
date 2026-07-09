<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Payment;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Tarik Data Pembayaran')] class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Payment::orderBy('id', 'desc');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                  ->orWhere('no_spm', 'like', '%' . $this->search . '%')
                  ->orWhere('keperluan', 'like', '%' . $this->search . '%');
            });
        }

        return [
            'payments' => $query->paginate(10)
        ];
    }
}; ?>
<div>
    <!-- Page Header -->
    <div style="margin-bottom: 28px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em; line-height:1.2;">Cetak Dokumen Pembayaran</h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Tarik data dari API Aplikasi Mailing Sudin untuk dicetak.</p>
        </div>
        
        <!-- Form Tarik Data Payment -->
        <div style="background: white; padding: 12px 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display:flex; flex-direction:column; gap: 8px;">
            <!-- Form ID Tunggal -->
            <form action="{{ route('payments.sync', 631) }}" method="POST" onsubmit="this.action='/payments/sync/'+document.getElementById('api_id').value;" style="display:flex; gap: 8px; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                @csrf
                <label for="api_id" style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); width:130px;">Berdasarkan ID API:</label>
                <input type="number" id="api_id" value="631" required style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; width: 80px; font-size: 0.9rem;">
                <button type="submit" class="btn btn-primary btn-sm">Tarik</button>
            </form>
            
            <!-- Form Range Tanggal -->
            <form action="{{ route('payments.sync-batch') }}" method="POST" style="display:flex; gap: 8px; align-items: center;">
                @csrf
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); width:130px;">Berdasarkan Tgl Dibuat:</label>
                <input type="date" name="start_date" required style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem;">
                <span style="font-size:0.8rem; color:var(--text-muted);">s/d</span>
                <input type="date" name="end_date" required style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem;">
                <button type="submit" class="btn btn-success btn-sm" style="background:var(--success); color:white;">Tarik Banyak</button>
            </form>
        </div>
    </div>

    @if(session('error'))
        <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div style="background: #d1fae5; color: #047857; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Recent Payments -->
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <h2 class="card-title" style="display:flex;align-items:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:var(--primary);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Data yang Pernah Ditarik
            </h2>
            <div style="width: 300px; max-width:100%;">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ID, No SPM, atau Keperluan..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; width: 100%; font-size:0.9rem;">
            </div>
        </div>

        @if($payments->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID API</th>
                            <th>No. SPM</th>
                            <th>Tanggal SPM</th>
                            <th>Keperluan</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td style="font-weight: bold;">{{ $payment->id }}</td>
                                <td style="color:var(--text-muted); font-family:'Courier New',monospace; font-size:0.8rem;">
                                    {{ $payment->no_spm ?? '-' }}
                                </td>
                                <td>
                                    {{ $payment->tgl_spm?->format('d M Y') ?? '-' }}
                                </td>
                                <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $payment->keperluan }}">
                                    {{ $payment->keperluan ?? '-' }}
                                </td>
                                <td style="font-weight: 600; color:var(--text-primary);">
                                    Rp {{ number_format($payment->jumlah, 0, ',', '.') }}
                                </td>
                                <td>
                                    <a href="{{ route('payments.print', $payment->id) }}" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                        Cetak
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="padding: 16px; border-top: 1px solid var(--border-color);">
                {{ $payments->links('vendor.pagination.custom', data: ['scrollTo' => false]) }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="empty-state-text">Belum ada data pembayaran yang ditemukan</div>
                <div class="empty-state-hint">Silakan cari kata kunci lain atau tarik data dari API</div>
            </div>
        @endif
    </div>

    <style>
        /* Custom Pagination Styles */
        .custom-pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 4px;
        }
        .custom-pagination .page-item {
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-primary);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .custom-pagination button.page-item:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary);
        }
        .custom-pagination .page-item.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .custom-pagination .page-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8fafc;
        }
    </style>
</div>
