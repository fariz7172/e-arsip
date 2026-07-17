<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected $casts = [
        'scan_file' => 'array',
        'detail_acaras' => 'array',
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function getFormattedDisposisiAttribute()
    {
        if (empty($this->disposisi)) {
            return '-';
        }

        $decoded = json_decode($this->disposisi, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $items = $decoded['checkboxes'] ?? [];
            $custom = $decoded['custom'] ?? '';
            
            $result = implode(', ', $items);
            if (!empty($custom)) {
                $result .= ($result ? ', ' : '') . $custom;
            }
            return $result ?: '-';
        }

        return $this->disposisi;
    }

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }
}
