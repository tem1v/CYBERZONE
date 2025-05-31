<?php
include 'db/db.php'; // путь к твоему подключению к БД

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';

if (!$q) {
    echo json_encode([]);
    exit;
}

// Поиск категорий
$categoryStmt = $pdo->prepare("SELECT id, name, 'category' as type FROM categories WHERE name LIKE ?");
$categoryStmt->execute(["%$q%"]);
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Поиск товаров
$productStmt = $pdo->prepare("SELECT id, name, 'product' as type FROM products WHERE name LIKE ?");
$productStmt->execute(["%$q%"]);
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);



// Объединяем
$results = array_merge($categories, $products);

echo json_encode($results);
