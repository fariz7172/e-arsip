<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggaran extends Model
{
    protected $fillable = ['parent_id', 'tipe', 'kode', 'nama', 'pagu'];

    public function parent()
    {
        return $this->belongsTo(Anggaran::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Anggaran::class, 'parent_id');
    }
}
