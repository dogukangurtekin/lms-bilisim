<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=school_management', 'root', '');
$stmt = $pdo->query('SELECT u.id, u.name, u.email, r.name AS role_name FROM users u LEFT JOIN roles r ON r.id = u.role_id ORDER BY u.id LIMIT 10');
foreach ($stmt as $row) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
