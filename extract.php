<?php
$lines = file('C:/Users/Fariz/.gemini/antigravity-ide/brain/38dc3eb5-8285-425c-99e5-8ab46a5583b1/.system_generated/logs/transcript.jsonl');
foreach($lines as $line) {
    $j = json_decode($line, true);
    if (isset($j['tool_calls'])) {
        foreach($j['tool_calls'] as $call) {
            if ($call['name'] == 'default_api:replace_file_content' || $call['name'] == 'default_api:multi_replace_file_content' || $call['name'] == 'default_api:write_to_file' || $call['name'] == 'default_api:view_file') {
                $args = is_array($call['arguments']) ? json_encode($call['arguments']) : $call['arguments'];
                if (strpos($args, 'payments/index.blade.php') !== false) {
                    echo "FOUND TOOL CALL:\n";
                    echo substr($args, 0, 500) . "...\n\n";
                }
            }
        }
    }
    if (isset($j['output']) && strpos($j['output'], 'payments/index.blade.php') !== false) {
        echo "FOUND OUTPUT:\n";
        echo substr($j['output'], 0, 2000) . "...\n\n";
    }
}
