<?php
$html = file_get_contents(__DIR__ . '/../storage/app/builder-form-render.html');
if (!preg_match('/id="lesson_payload"[^>]*value="([^"]*)"/', $html, $m)) { echo "no-hidden\n"; exit; }
$raw = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
file_put_contents(__DIR__ . '/../storage/app/hidden-decoded.txt', $raw);
echo strlen($raw), PHP_EOL;
?>
