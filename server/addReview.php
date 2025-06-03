<?php
session_start();
include 'db/db.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
$productId = $_POST['product_id'] ?? null;
$orderId = $_POST['order_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = $_POST['comment'] ?? '';

if (!$userId || !$productId || !$orderId || !$rating) {
    echo json_encode(['success' => false, 'message' => 'Недостаточно данных']);
    exit;
}

// Проверяем, есть ли уже отзыв для этого конкретного заказа
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? AND order_id = ?");
$stmt->execute([$userId, $productId, $orderId]);
$exists = $stmt->fetch();

if ($exists) {
    // Обновляем отзыв для конкретного заказа
    $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE user_id = ? AND product_id = ? AND order_id = ?");
    $stmt->execute([$rating, $comment, $userId, $productId, $orderId]);
} else {
    // Вставляем новый отзыв с привязкой к заказу
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, order_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $productId, $orderId, $rating, $comment]);
}

echo json_encode(['success' => true]);
