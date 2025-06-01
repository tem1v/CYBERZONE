<?php
session_start();
include '../server/db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirectUrl = $_POST['redirect'] ?? '../mainPage.php';

    // Массив для ошибок
    $errors = [];

    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
        $errors[] = "Пожалуйста, заполните все поля.";
    }

    // Проверка, есть ли пользователь с таким email или телефоном
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $phone]);
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        $errors[] = "Пользователь с такой почтой или телефоном уже существует.";
    }

    if (!empty($errors)) {
        // Сохраняем ошибки в сессии
        $_SESSION['registration_errors'] = $errors;
        // Сохраняем введенные данные, чтобы не вводить заново
        $_SESSION['registration_data'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
        ];
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Если ошибок нет - создаем пользователя
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, phone, password)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword]);

    $userId = $pdo->lastInsertId();

    $_SESSION['user_id'] = $userId;
    $_SESSION['first_name'] = $firstName;

    header('Location: ' . $redirectUrl);
    exit;
} else {
    echo "Неверный метод запроса.";
}
