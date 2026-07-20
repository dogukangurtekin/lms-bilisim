<?php

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=school_management', 'root', '');
$stmt = $pdo->query("SHOW TABLES LIKE 'sessions'");
$rows = $stmt->fetchAll(PDO::FETCH_NUM);
echo count($rows) > 0 ? "exists\n" : "missing\n";
