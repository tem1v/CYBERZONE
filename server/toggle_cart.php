<?php
session_start();
include '../server/db/db.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !isset($_POST['product_id'])) {
	echo json_encode(['error' => true]);
	exit;
}

$productId = $_POST['product_id'];

// Проверка: есть ли товар уже в корзине
$stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$item = $stmt->fetch();

if ($item) {
	// Удалить
	$pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?")->execute([$userId, $productId]);
	$inCart = false;
} else {
	// Добавить
	$pdo->prepare("INSERT INTO cart_items (user_id, product_id) VALUES (?, ?)")->execute([$userId, $productId]);
	$inCart = true;
}

echo json_encode(['in_cart' => $inCart]);