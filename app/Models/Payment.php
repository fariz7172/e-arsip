<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tgl_spd' => 'date',
        'tgl_spp' => 'date',
        'tgl_spm' => 'date',
        'tgl_sp2d' => 'date',
        'tgl_bast' => 'date',
        'tgl_kwi' => 'date',
        'jumlah' => 'decimal:2',
        'tagihan_1' => 'decimal:2',
        'tagihan_2' => 'decimal:2',
        'tagihan_3' => 'decimal:2',
        'tagihan_4' => 'decimal:2',
        'tagihan_5' => 'decimal:2',
        'denda' => 'decimal:2',
        'print_data' => 'array',
        'vendor' => 'object',
        'contract' => 'object',
        'pptk' => 'object',
        'program_ref' => 'object',
        'kegiatan_ref' => 'object',
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        try {
            $id = \Illuminate\Support\Facades\Crypt::decryptString($value);
            return $this->where($field ?? $this->getRouteKeyName(), $id)->firstOrFail();
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(403, 'Invalid or corrupted link.');
        }
    }
}
