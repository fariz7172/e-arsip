<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Payment;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Laporan Berkas (Daftar Isi Berkas)')] class extends Component {
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $perPage = 10;

    public $selectedPayments = [];
    public $selectAll = false;

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPayments = $this->with()['payments']->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedPayments = [];
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Payment::with('bundle')->orderBy('id', 'desc');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                  ->orWhere('no_spm', 'like', '%' . $this->search . '%')
                  ->orWhere('keperluan', 'like', '%' . $this->search . '%');
            });
        }

        return [
            'payments' => $query->paginate($this->perPage)
        ];
    }
}; ?>
<div>
    <!-- Page Header -->
    <div style="margin-bottom: 28px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em; line-height:1.2;">Laporan Berkas</h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Daftar Isi Berkas untuk masing-masing pembayaran.</p>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <h2 class="card-title" style="display:flex;align-items:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:var(--primary);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Pilih Pembayaran
                
                @if(count($selectedPayments) > 0)
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('laporan-spn.print-batch') }}?ids={{ implode(',', $selectedPayments) }}" target="_blank" class="btn btn-primary btn-sm" style="background:var(--primary); color:white; display:inline-flex; align-items:center; gap:6px; margin-left: 12px; font-size: 0.8rem; padding: 4px 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                            Cetak Laporan SPN ({{ count($selectedPayments) }})
                        </a>
                        <a href="{{ route('laporan-spn.print-arsip') }}?ids={{ implode(',', $selectedPayments) }}" target="_blank" class="btn btn-success btn-sm" style="background:#10b981; color:white; display:inline-flex; align-items:center; gap:6px; font-size: 0.8rem; padding: 4px 10px; border-radius: 6px; text-decoration: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            Cetak Daftar Arsip ({{ count($selectedPayments) }})
                        </a>
                    </div>
                @endif
            </h2>
            <div style="display: flex; gap: 12px; align-items: center; width: 100%; max-width: 400px; justify-content: flex-end;">
                <select wire:model.live="perPage" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; font-size:0.9rem; background: white; cursor: pointer;">
                    <option value="10">10 Data</option>
                    <option value="15">15 Data</option>
                    <option value="50">50 Data</option>
                    <option value="100">100 Data</option>
                    <option value="500">500 Data</option>
                </select>
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ID, No SPM, atau Keperluan..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; width: 100%; font-size:0.9rem;">
                </div>
            </div>
        </div>

        @if($payments->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" wire:model.live="selectAll" style="cursor: pointer;">
                            </th>
                            <th style="width: 80px;">ID</th>
                            <th>No. SPM</th>
                            <th>Nilai Pembayaran</th>
                            <th>Keperluan</th>
                            <th style="text-align:center;">Laporan Berkas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr wire:key="payment-{{ $payment->id }}" class="{{ in_array($payment->id, $selectedPayments) ? 'bg-slate-50' : '' }}">
                                <td style="text-align: center;">
                                    <input type="checkbox" wire:model.live="selectedPayments" value="{{ $payment->id }}" style="cursor: pointer;">
                                </td>
                                <td style="font-weight: 600; color:var(--text-secondary);">#{{ $payment->id }}</td>
                                <td>
                                    <a href="{{ route('payments.print', \Illuminate\Support\Facades\Crypt::encryptString($payment->id)) }}" style="font-weight: 600; color:var(--primary); text-decoration: none; cursor: pointer;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                        {{ $payment->no_spm ?? '-' }}
                                    </a>
                                    <div style="font-size:0.8rem; color:var(--text-muted);">{{ $payment->tgl_spm ? \Carbon\Carbon::parse($payment->tgl_spm)->format('d/m/Y') : '-' }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color:var(--success);">Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</div>
                                    <div style="font-size:0.8rem; color:var(--text-muted); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $payment->terbilang }}">{{ $payment->terbilang }}</div>
                                </td>
                                <td>
                                    <div style="font-size:0.85rem; color:var(--text-secondary); max-height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="{{ $payment->keperluan }}">
                                        {{ $payment->keperluan }}
                                    </div>
                                    @if($payment->bundle && $payment->dokumen_id)
                                        <a href="{{ route('dokumen.show', \Illuminate\Support\Facades\Crypt::encryptString($payment->dokumen_id)) }}" style="margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; background: var(--primary-light); color: var(--primary); padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-decoration: none; transition: 0.2s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white';" onmouseout="this.style.background='var(--primary-light)'; this.style.color='var(--primary)';">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                            Lihat File di: {{ $payment->bundle->kode ?? $payment->bundle->nama }}
                                        </a>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <a href="{{ route('laporan-spn.print', \Illuminate\Support\Facades\Crypt::encryptString($payment->id)) }}" target="_blank" class="btn btn-primary btn-sm" style="background:var(--primary); color:white; display:inline-flex; align-items:center; gap:6px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                        Cetak Laporan 
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
            <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width: 48px; height: 48px; margin: 0 auto 12px; opacity: 0.5;">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <p>Belum ada data pembayaran. Silakan tarik data terlebih dahulu.</p>
            </div>
        @endif
    </div>
</div>
