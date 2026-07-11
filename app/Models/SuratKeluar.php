<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scan_file' => 'array',
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }
}
