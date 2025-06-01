<?php
session_start();
include 'db/db.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
    exit;
}

try {
    // Получаем товары с учётом скидки
    $stmt = $pdo->prepare("
        SELECT 
            p.id as product_id,
            ci.quantity,
            -- Расчёт цены со скидкой
            ROUND(p.price * (1 - IFNULL(p.discount_percent, 0) / 100), 2) AS discounted_price
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();

    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'Корзина пуста']);
        exit;
    }

    $pdo->beginTransaction();

    $insertStmt = $pdo->prepare("
        INSERT INTO orders (user_id, product_id, price_at_order, ordered_at)
        VALUES (?, ?, ?, NOW())
    ");

    foreach ($cartItems as $item) {
        $insertStmt->execute([
            $userId,
            $item['product_id'],
            $item['discounted_price']
        ]);
    }

    $deleteStmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $deleteStmt->execute([$userId]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Заказ успешно оформлен']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Ошибка при оформлении заказа: ' . $e->getMessage()]);
}
?>


