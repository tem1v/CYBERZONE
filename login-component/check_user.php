<?php
	session_start();
	include '../server/db/db.php';

	if (isset($_POST['identifier'])) {
		$identifier = $_POST['identifier'];
		$redirectUrl = $_POST['redirect'] ?? '../mainPage.php';
	
		
		$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id OR phone = :id");
		$stmt->execute(['id' => $identifier]);
		$user = $stmt->fetch();
	
		if ($user) {
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['first_name'] = $user['first_name'];
    		header('Location: ' . $redirectUrl);
    		exit;
		} else {
			echo "Пользователь не найден.";
		}
	} else {
		echo "Поле не заполнено.";
	}
?>