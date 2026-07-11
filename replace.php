<?php
$file = "d:/program file/Project Kantor/e-arsip/resources/views/pages/laporan-spn/print.blade.php";
$content = file_get_contents($file);

$content = preg_replace("/Pek\.?\s?Perbaikan Bangunan Jaga Pintu Air Marina/", "{{ \$payment->keperluan ?? '...' }}", $content);

file_put_contents($file, $content);
echo "Replacement successful!\n";
