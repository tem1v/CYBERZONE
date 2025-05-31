<?php
session_start();
include 'db/db.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
$productId = $_POST['product_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = $_POST['comment'] ?? '';

if (!$userId || !$productId || !$rating) {
    echo json_encode(['success' => false, 'message' => 'Недостаточно данных']);
    exit;
}

// Проверяем, есть ли уже отзыв
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$exists = $stmt->fetch();

if ($exists) {
    // Обновляем
    $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$rating, $comment, $userId, $productId]);
} else {
    // Вставляем
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $productId, $rating, $comment]);
}

echo json_encode(['success' => true]);
