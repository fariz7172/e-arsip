<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FileAttachment extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::deleting(function ($fileAttachment) {
            if (\Illuminate\Support\Facades\Storage::disk($fileAttachment->disk)->exists($fileAttachment->path)) {
                \Illuminate\Support\Facades\Storage::disk($fileAttachment->disk)->delete($fileAttachment->path);
            }
        });
    }

    protected $fillable = [
        'dokumen_id',
        'nama_file',
        'path',
        'disk',
        'mime_type',
        'ukuran',
    ];

    protected $casts = [
        'ukuran' => 'integer',
    ];

    /**
     * File belongs to a dokumen.
     */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }

    /**
     * Get formatted file size.
     */
    public function getUkuranFormatAttribute(): string
    {
        $bytes = $this->ukuran;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * Check if this is an image file.
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if this is a PDF file.
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get file extension.
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->nama_file, PATHINFO_EXTENSION);
    }
}
