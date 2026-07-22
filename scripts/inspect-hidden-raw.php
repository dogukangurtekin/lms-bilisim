<?php
$html = file_get_contents(__DIR__ . '/../storage/app/builder-form-render.html');
if (!preg_match("/id='lesson_payload'[^>]*value='([^']*)'/", $html, $m)) { echo "no-hidden\n"; exit; }
$raw = $m[1];
echo substr($raw, 0, 250), PHP_EOL;
?>
