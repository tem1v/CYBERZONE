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
				<form method="post" action="login-component/check_user.php">
					<input type="email" name="email" placeholder="E-mail" required>
					<input type="password" name="password" placeholder="Пароль" required>
					<input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
					<span id="login-error" style="color: red;">
						<?php 
						if (isset($_SESSION['login_error'])) {
							echo htmlspecialchars($_SESSION['login_error']);
							unset($_SESSION['login_error']);
						}
						?>
					</span>
					<button type="submit" class="login-button">Войти</button>
				</form>
				<p>Нет аккаунта? <a href="#" onclick="switchToRegister()">Зарегистрируйтесь</a></p>
				<button onclick="closeModal()" class="close-button">Закрыть</button>
			</div>
		</div>

		<div id="register-modal" class="modal-overlay" style="display: none;">
			<div class="modal-content">
				<h2>Регистрация</h2>
				<form method="post" action="login-component/registration.php">
					<input type="text" name="first_name" placeholder="Имя" required
						value="<?= htmlspecialchars($_SESSION['registration_data']['first_name'] ?? '') ?>">
					<input type="text" name="last_name" placeholder="Фамилия" required
						value="<?= htmlspecialchars($_SESSION['registration_data']['last_name'] ?? '') ?>">
					<input type="email" name="email" placeholder="E-mail" required
						value="<?= htmlspecialchars($_SESSION['registration_data']['email'] ?? '') ?>">
					<input type="tel" name="phone" placeholder="Телефон" required
						value="<?= htmlspecialchars($_SESSION['registration_data']['phone'] ?? '') ?>">
					<input type="password" name="password" placeholder="Пароль" required>
					<input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
					<span id="registration-error" style="color: red;">
						<?php 
						if (!empty($_SESSION['registration_errors'])) {
							foreach ($_SESSION['registration_errors'] as $error) {
								echo htmlspecialchars($error) . '<br>';
							}
							unset($_SESSION['registration_errors']);
						}
						?>
					</span>
					<button type="submit" class="login-button">Зарегистрироваться</button>
				</form>
				<p>Уже есть аккаунт? <a href="#" onclick="switchToLogin()">Войти</a></p>
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
				document.getElementById('register-modal').style.display = 'none';
				document.body.style.overflow = '';
			}

			function switchToRegister() {
				document.getElementById('login-modal').style.display = 'none';
				document.getElementById('register-modal').style.display = 'flex';
			}

			function switchToLogin() {
				document.getElementById('register-modal').style.display = 'none';
				document.getElementById('login-modal').style.display = 'flex';
			}

			document.querySelectorAll('.cart-link, .account-link, .favorites-logo a').forEach(link => {
				link.addEventListener('click', showModalIfNotLoggedIn);
			});
			window.addEventListener('DOMContentLoaded', () => {
				const errorSpan = document.getElementById('login-error');
				if (errorSpan && errorSpan.textContent.trim() !== '') {
					document.getElementById('login-modal').style.display = 'flex';
					document.body.style.overflow = 'hidden';
				}
			});

			window.addEventListener('DOMContentLoaded', () => {
				const regErrorSpan = document.getElementById('registration-error');
				if (regErrorSpan && regErrorSpan.textContent.trim() !== '') {
					document.getElementById('register-modal').style.display = 'flex';
					document.body.style.overflow = 'hidden';
				}
			});
		</script>
	</body>
</html>