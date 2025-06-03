<?php
	include 'server/db/db.php';
	session_start();


	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	$userId = $_SESSION['user_id'] ?? null;
	// Проверка наличия ID
	if (!$userId) {
		echo "Пользователь не найден.";
		exit;
	}

	$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
	$stmt->execute([$userId]);
	$user = $stmt->fetch();

	$stmt = $pdo->prepare("
		SELECT o.*, p.name, p.image_path, r.rating
		FROM orders o
		JOIN products p ON o.product_id = p.id
		LEFT JOIN reviews r ON r.product_id = p.id AND r.user_id = o.user_id AND r.order_id = o.id
		WHERE o.user_id = ?
		ORDER BY o.ordered_at DESC
	");
	$stmt->execute([$userId]);
	$orders = $stmt->fetchAll();

	$categories = [];
	$query = $pdo->query("SELECT id, name FROM categories");
	if ($query) {
		$categories = $query->fetchAll(PDO::FETCH_ASSOC);
	}
	$cartItems = [];
	$favItems = [];

	if ($userId) {
		$stmt = $pdo->prepare("SELECT product_id FROM cart_items WHERE user_id = ?");
		$stmt->execute([$userId]);
		$cartItems = $stmt->fetchAll(PDO::FETCH_COLUMN);

		$stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
		$stmt->execute([$userId]);
		$favItems = $stmt->fetchAll(PDO::FETCH_COLUMN);
	}
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles/profile.css">
	<title>Cyberzone</title>
	<link rel="shortcut icon" href="img/logo/cyberzone_icon.png">
</head>
<body>
<header class="header-container">
		<div class="logo">
			<a href="mainPage.php">
				<img src="img/logo/logo.png" alt="" height="40px">
				<span class="logo-text">CYBERZONE</span>
			</a>
		</div>
		<form action="search.php" class="search-container" method="GET">
			<input type="text" name="q" id="searchInput" class="search-input" placeholder="Поиск...">
			<button class="search-button" type="submit">
				<img src="img/icons/search.png" height="20" alt="Поиск">
			</button>
			<div id="searchResults" class="search-results"></div>
		</form>
		<nav class="navigation">
			<div class="action-words">
				<a href="discount.php">Скидки</a>
				<div onclick="showCategories()" class="category-drop-down" id="categoryDropdownTrigger">
					<span>Категории</span>
					<div class="dropdown-menu" id="dropdown">
						<?php foreach ($categories as $category): ?>
							<a href="catalog.php?category=<?= $category['id'] ?>" class="dropdown-item">
								<?= htmlspecialchars($category['name']) ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

			</div>
			<?php if (isset($_SESSION['user_id'])): ?>
				<div class="action-icons">
					<div class="favorites-logo">
					<a href="favorites.php">
						<img src="img/icons/heart_white.png" height="30px">
					</a>
					</div>
					<div class="cart-logo">
					<a href="cart.php" class="cart-link">
						<img src="img/icons/shopping-cart_white.png" height="30px" alt="Корзина">
						<span class="cart-counter"><?= count($cartItems) ?></span>
					</a>
					</div>
					<div class="account-logo">
					<a href="profile.php?id=<?= $userId ?>" class="account-link">
						<img src="img/icons/user.png" height="30px" alt="Аккаунт">
						<span class="account-name"><?= htmlspecialchars($_SESSION['first_name'] ?? 'Профиль') ?></span>
					</a>
					</div>
				</div>
				<?php else: ?>
				<div class="action-icons">
					<div class="favorites-logo">
					<a href="#" onclick="openLoginModal(event)">
						<img src="img/icons/heart_white.png" height="30px">
					</a>
					</div>
					<div class="cart-logo">
					<a href="#" class="cart-link" onclick="openLoginModal(event)">
						<img src="img/icons/shopping-cart_white.png" height="30px" alt="Корзина">
						<span class="cart-counter-default">0</span>
					</a>
					</div>
					<div class="account-logo">
					<a href="#" class="account-link" onclick="openLoginModal(event)">
						<img src="img/icons/user.png" height="30px" alt="Аккаунт">
						<span class="account-name">Войти</span>
					</a>
					</div>
				</div>
			<?php endif; ?>
		</nav>
		
	</header>



	<main>
		<div class="profile-page">
			<div class="profile-info">
				<span class="welcome-span">Добро пожаловать, <?= htmlspecialchars($user['first_name']) ?></span>
				<div class="info-tablet">
					<div class="name-info">
						<span class="name">Имя:</span>
						<span class="value" data-field="first_name"><?= htmlspecialchars($user['first_name']) ?></span>
					</div>
					<div class="surname-info">
						<span class="surname">Фамилия:</span>
						<span class="value" data-field="last_name"><?= htmlspecialchars($user['last_name']) ?></span>
					</div>
					<div class="email-info">
						<span class="email">Email:</span>
						<span class="value" data-field="email"><?= htmlspecialchars($user['email']) ?></span>
					</div>
					<div class="phone-info">
						<span class="phone">Номер телефона:</span>
						<span class="value" data-field="phone"><?= htmlspecialchars($user['phone']) ?></span>
					</div>
					<div class="buttons">
						<button class="change-info-btn">Изменить</button>
						<button class="delete-account-btn">Выйти</button>
					</div>
				</div>
			</div>
			<div class="orders-history">
				<span class="orders-history-span">История заказов</span>
				<div class="orders-history-list">
					<?php if (empty($orders)): ?>
						<h3 class="empty-orders">Вы еще не делали заказов.</h3>
					<?php else: ?>
						<?php foreach ($orders as $order): ?>
							<div class="order-cart" data-product-id="<?= $order['product_id'] ?>" data-order-id="<?= $order['id'] ?>">
								<a href="goodPage.php?id=<?= $order['product_id'] ?>">
									<img src="<?= $order['image_path'] ?>" height="220px">
								</a>
								<div class="order-info">
									<span class="order-date"><?= date('d.m.Y', strtotime($order['ordered_at'])) ?></span>
									<span class="order-name"><?= htmlspecialchars($order['name']) ?></span>
									<div class="rate">
										<span class="rate-span">Оцените товар</span>
										<div class="stars">
											<?php
											$rating = $order['rating'] ?? 0; // оценка или 0, если нет
											for ($i = 1; $i <= 5; $i++): ?>
												<button class="star-btn" data-rating="<?= $i ?>">
													<img src="img/icons/<?= ($i <= $rating) ? 'star_yellow' : 'star_gray' ?>.png" height="40px">
												</button>
											<?php endfor; ?>
										</div>
									</div>
									<span class="price"><?= number_format($order['price_at_order'], 0, '', ' ') ?> р.</span>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</main>




	<footer>
		<div class="footer-logo-slogan">
			<div class="footer-logo">
				<img src="img/logo/logo.png" alt="" height="62px">
				<span class="footer-logo-text">CYBERZONE</span>
			</div>
			<span class="footer-slogan-text">Лучший магазин техники и аксессуаров для киберспортсменов. Покупай — побеждай!</span>
		</div>
		<hr>
		<div class="footer-columns">
			<div class="footer-column-buyer">
				<span class="buyer-column-label">Покупателю</span>
				<ul class="buyer-column-list">
					<li>Акции и скидки</li>
					<li>Наши магазины</li>
					<li>Доставка</li>
					<li>Кредит и рассрочка</li>
					<li>Частые вопросы</li>
					<li>Возврат и обмен</li>
					<li>Подарочные карты</li>
					<li>Статус заказа</li>
				</ul>
			</div>
			<div class="footer-column-company">
				<span class="company-column-label">О компании</span>
				<ul class="company-column-list">
					<li>Тендеры</li>
					<li>Политика компании</li>
					<li>Вакансии</li>
					<li>Партнерская программа</li>
					<li>Поставщикам</li>
					<li>Реклама на сайте</li>
					<li>Аренда</li>
				</ul>
			</div>
			<div class="footer-column-cyberblog">
				<span class="cyberblog-column-label">CYBERBLOG</span>
				<ul class="cyberblog-column-list">
					<li>Asus обновила линейку игровых ноутбуков</li>
					<li>Acer выпустит 14-дюймовые игровые ноутбуки Predator Triton и Predator Helios</li>
					<li>Топ-5 игр для слабых ПК</li>
					<li>Rockstar опубликовала 
						второй трейлер GTA 6</li>
					<li>В Алматы пройдет турнир 
						по киберспорту ACG 
						Media & Cup 2025</li>
				</ul>
			</div>
			<div class="footer-column-contacts">
				<span class="contacts-column-label">Контакты</span>
				<ul class="contacts-column-list">
					<li>8-800-888-8888</li>
					<li>Whatsapp</li>
					<li>Telegram</li>
					<li>ВК</li>
					<li>cyberzone@mail.ru</li>
					<li>г. Казань</li>
				</ul>
			</div>
		</div>
	</footer>
</body>
<script src="js/updateProfileInfo.js"></script>
<script src="js/addReview.js"></script>
<script src="js/search.js"></script>
<script src="js/showCategories.js"></script>
</html>