<?php
session_start();
include '../server/db/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Войдите в аккаунт.";
    exit;
}

if (isset($_POST['id'])) {
    $productId = (int)$_POST['id'];
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (:user_id, :product_id)");
    $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);

    echo "Товар добавлен в избранное.";
} else {
    echo "Ошибка: товар не найден.";
}