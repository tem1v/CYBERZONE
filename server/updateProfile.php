<?php
session_start();
include 'db/db.php';

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Вы не авторизованы']);
    exit;
}

$userId = $_SESSION['user_id'];

$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');

// Проверка на пустые поля
if (empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны']);
    exit;
}

// Валидация email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email']);
    exit;
}

// Валидация телефона (+7 и 11 цифр)
if (!preg_match('/^\+7\d{10}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Телефон должен начинаться с +7 и содержать 11 цифр']);
    exit;
}

// Проверка уникальности email и телефона (исключая текущего пользователя)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (email = ? OR phone = ?) AND id != ?");
$stmt->execute([$email, $phone, $userId]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Email или телефон уже используются другим пользователем']);
    exit;
}

// Обновление пользователя
$stmt = $pdo->prepare("
    UPDATE users
    SET first_name = ?, last_name = ?, email = ?, phone = ?
    WHERE id = ?
");

try {
    $stmt->execute([$firstName, $lastName, $email, $phone, $userId]);
	$_SESSION['first_name'] = $firstName;
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении данных']);
}

