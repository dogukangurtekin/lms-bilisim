<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=school_management', 'root', '');
$stmt = $pdo->query("SELECT id, name, JSON_UNQUOTE(JSON_EXTRACT(lesson_payload, '$.cover_image')) AS cover FROM courses WHERE JSON_EXTRACT(lesson_payload, '$.cover_image') IS NOT NULL LIMIT 10");
foreach ($stmt as $row) { echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL; }
