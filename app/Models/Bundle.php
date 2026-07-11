<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bundle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'tahun',
        'created_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    /**
     * Bundle belongs to a user (creator).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'id_lokasi');
    }

    public function suratMasuks(): HasMany
    {
        return $this->hasMany(SuratMasuk::class);
    }

    public function suratKeluars(): HasMany
    {
        return $this->hasMany(SuratKeluar::class);
    }

    /**
     * Bundle has many kategoris.
     */
    public function kategoris(): HasMany
    {
        return $this->hasMany(Kategori::class)->orderBy('urutan');
    }

    /**
     * Bundle has many payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all dokumens through kategoris.
     */
    public function dokumens(): HasManyThrough
    {
        return $this->hasManyThrough(Dokumen::class, Kategori::class);
    }

    /**
     * Scope by tahun.
     */
    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Get total file count.
     */
    public function getTotalFilesAttribute(): int
    {
        return $this->dokumens->sum(fn($dok) => $dok->fileAttachments->count());
    }
}
