<?php
$file = 'C:/Users/PC USER/.gemini/antigravity-ide/brain/752a2e7c-48bd-4a04-ae6a-a349bb224596/.system_generated/logs/transcript.jsonl';
if (!file_exists($file)) die("No file\n");
$lines = file($file);
foreach ($lines as $l) {
    $data = json_decode($l, true);
    if (($data['type'] ?? '') === 'PLANNER_RESPONSE' && strpos($data['content'] ?? '', 'Option 1') !== false) {
        echo "=== PLANNER RESPONSE ===\n";
        echo substr($data['content'], 0, 2000) . "\n";
        break;
    }
}

