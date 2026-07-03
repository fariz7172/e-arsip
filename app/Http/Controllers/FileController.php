<?php

namespace App\Http\Controllers;

use App\Models\FileAttachment;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Download a file attachment.
     */
    public function download(FileAttachment $file)
    {
        if (!Storage::disk($file->disk)->exists($file->path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk($file->disk)->download($file->path, $file->nama_file);
    }

    /**
     * Preview/stream a file attachment (inline in browser).
     */
    public function preview(FileAttachment $file)
    {
        if (!Storage::disk($file->disk)->exists($file->path)) {
            abort(404, 'File tidak ditemukan.');
        }

        if ($file->disk === 'local' || $file->disk === 'public') {
            $path = Storage::disk($file->disk)->path($file->path);
            $response = response()->file($path, [
                'Content-Type' => $file->mime_type,
            ]);
            
            $response->setContentDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_INLINE,
                $file->nama_file
            );
            
            return $response;
        }

        return Storage::disk($file->disk)->response($file->path, $file->nama_file, [
            'Content-Type' => $file->mime_type,
        ], 'inline');
    }
}
