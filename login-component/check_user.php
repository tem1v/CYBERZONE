<?php
session_start();
include '../server/db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirectUrl = $_POST['redirect'] ?? '../mainPage.php';

    if ($email === '' || $password === '') {
        $_SESSION['login_error'] = "Пожалуйста, заполните все поля.";
        header("Location: $redirectUrl");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        header("Location: $redirectUrl");
        exit;
    } else {
        $_SESSION['login_error'] = "Неверный email или пароль.";
        header("Location: $redirectUrl");
        exit;
    }
} else {
    echo "Неверный метод запроса.";
}

