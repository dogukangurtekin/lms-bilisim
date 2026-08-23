<?php

$f = file_get_contents(__DIR__ . '/../storage/app/app-inventor-ile-mobil-kodlama.coursepkg');
if ($f === false) {
    fwrite(STDERR, "missing\n");
    exit(1);
}
if (!preg_match('/^COURSEPKG2\r?\nBOUNDARY:(.+?)\r?\n\r?\n/s', $f, $m)) {
    fwrite(STDERR, "noheader\n");
    exit(1);
}
$boundary = trim($m[1]);
$parts = preg_split('/\r?\n--' . preg_quote($boundary, '/') . '(?:--)?\r?\n/', $f);
$manifest = '';
foreach ($parts as $part) {
    if (strpos($part, 'application/json') !== false) {
        $chunks = preg_split('/\r?\n\r?\n/', $part, 2);
        $manifest = $chunks[1] ?? '';
        break;
    }
}
$data = json_decode(trim($manifest), true);
if (!is_array($data)) {
    fwrite(STDERR, "jsonfail\n");
    exit(1);
}
echo 'slides=' . count($data['slides'] ?? []) . PHP_EOL;
echo 'course=' . ($data['course']['name'] ?? '') . PHP_EOL;
