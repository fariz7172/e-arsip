<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dokumen extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kategori_id',
        'judul',
        'nomor_dokumen',
        'tanggal_dokumen',
        'keterangan',
        'uploaded_by',
    ];

    protected $casts = [
        'tanggal_dokumen' => 'date',
    ];

    /**
     * Dokumen belongs to a kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Dokumen belongs to uploader user.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Dokumen has many file attachments.
     */
    public function fileAttachments(): HasMany
    {
        return $this->hasMany(FileAttachment::class);
    }

    /**
     * Get bundle through kategori.
     */
    public function getBundleAttribute()
    {
        return $this->kategori?->bundle;
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
