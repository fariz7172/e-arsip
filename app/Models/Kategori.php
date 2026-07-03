<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::deleting(function ($kategori) {
            \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory("arsip/{$kategori->bundle_id}/{$kategori->id}");
        });
    }

    protected $fillable = [
        'bundle_id',
        'nama',
        'kode',
        'deskripsi',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    /**
     * Kategori belongs to a bundle.
     */
    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    /**
     * Kategori has many dokumens.
     */
    public function dokumens(): HasMany
    {
        return $this->hasMany(Dokumen::class)->latest();
    }

    /**
     * Get dokumen count.
     */
    public function getDokumenCountAttribute(): int
    {
        return $this->dokumens()->count();
    }
}
