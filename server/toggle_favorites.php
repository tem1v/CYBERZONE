<?php
session_start();
include '../server/db/db.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !isset($_POST['product_id'])) {
	echo json_encode(['error' => true]);
	exit;
}

$productId = $_POST['product_id'];


$stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$item = $stmt->fetch();

if ($item) {
	$pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?")->execute([$userId, $productId]);
	$inFav = false;
} else {
	$pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)")->execute([$userId, $productId]);
	$inFav = true;
}

echo json_encode(['in_favorite' => $inFav]);