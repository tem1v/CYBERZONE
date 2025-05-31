<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="loginStyle.css">
		<title>Document</title>
	</head>
	<body>
		<div id="login-modal" class="modal-overlay" style="display: none;">
			<div class="modal-content">
				<h2>Вход в аккаунт</h2>
				<form method="post" action="login-component\check_user.php">
					<input type="text" name="identifier" placeholder="Телефон или e-mail" required>
					<input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
					<button type="submit" class="login-button">Войти</button>
				</form>
				<button onclick="closeModal()" class="close-button">Закрыть</button>
			</div>
		</div>
		<script>
			let isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

			function showModalIfNotLoggedIn(e) {
				if (!isLoggedIn) {
					e.preventDefault();
					document.getElementById('login-modal').style.display = 'flex';
				}
			}

			function closeModal() {
				document.getElementById('login-modal').style.display = 'none';
			}
			
			document.querySelectorAll('.cart-link, .account-link, .favorites-logo a').forEach(link => {
				link.addEventListener('click', showModalIfNotLoggedIn);
			});
		</script>
	</body>
</html>