<?php
$lines = file('C:/Users/Fariz/.gemini/antigravity-ide/brain/38dc3eb5-8285-425c-99e5-8ab46a5583b1/.system_generated/logs/transcript.jsonl');
foreach($lines as $line) {
    $j = json_decode($line, true);
    if (isset($j['output']) && strpos($j['output'], 'payments/index.blade.php') !== false) {
        echo "FOUND OUTPUT:\n";
        echo $j['output'] . "\n\n";
    }
}
