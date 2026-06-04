<?php
require 'config/db.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, brand FROM products WHERE name LIKE ? OR brand LIKE ? OR description LIKE ? LIMIT 5");
$searchTerm = "%$q%";
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);