<?php
$files = scandir(__DIR__);
$now = time();
echo "Current system time: " . date('Y-m-d H:i:s', $now) . "\n\n";
echo "Files modified in scratch:\n";
$list = [];
foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    $path = __DIR__ . '/' . $f;
    $mtime = filemtime($path);
    $diffMin = round(($now - $mtime) / 60);
    $list[] = [
        'name' => $f,
        'mtime' => $mtime,
        'date' => date('Y-m-d H:i:s', $mtime),
        'diffMin' => $diffMin
    ];
}

usort($list, function($a, $b) {
    return $b['mtime'] - $a['mtime'];
});

foreach ($list as $item) {
    echo sprintf("%-35s %s (%3d mins ago)\n", $item['name'], $item['date'], $item['diffMin']);
}
