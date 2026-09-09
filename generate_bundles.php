<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rekenings = \App\Models\Anggaran::where('tipe', 'rekening')->get();
$count = 0;
foreach($rekenings as $rek) {
    // Make sure kode is unique by appending ID if necessary
    $kodeUnique = $rek->kode . '-' . $rek->id;
    \App\Models\Bundle::firstOrCreate(
        ['anggaran_id' => $rek->id, 'tahun' => date('Y')],
        ['nama' => $rek->nama, 'kode' => $kodeUnique, 'created_by' => 1]
    );
    $count++;
}
echo "Created $count Bundles!";
