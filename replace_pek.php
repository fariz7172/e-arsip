<?php
$file = "d:/program file/Project Kantor/e-arsip/resources/views/pages/laporan-spn/print.blade.php";
$content = file_get_contents($file);

// Replace {{ $payment->keperluan ?? '...' }} with Pek. {{ $payment->keperluan ?? '...' }}
// But avoid doing it multiple times if it's already there
$content = preg_replace("/(?<!Pek\. )\s?\{\{ \\\$payment->keperluan \?\? '\.\.\.' \}\}/", " Pek. {{ \$payment->keperluan ?? '...' }}", $content);

// Clean up extra spaces that might have been introduced
$content = str_replace("  Pek.", " Pek.", $content);

file_put_contents($file, $content);
echo "Replacement successful!\n";
