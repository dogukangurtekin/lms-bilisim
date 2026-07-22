<?php
$html = file_get_contents(__DIR__ . '/../storage/app/builder-form-render.html');
if (!preg_match("/id='lesson_payload'[^>]*value='([^']*)'/", $html, $m)) { echo json_encode(['found'=>false], JSON_PRETTY_PRINT), PHP_EOL; exit; }
$raw = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
$data = json_decode($raw, true);
echo json_encode([
  'found' => true,
  'raw_start' => substr($raw, 0, 80),
  'json_ok' => is_array($data),
  'slides_count' => count((array)($data['slides'] ?? [])),
  'keys' => array_keys((array)$data),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
?>
