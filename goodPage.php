<?php
	include 'server/db/db.php';
	session_start();
	$isLoggedIn = isset($_SESSION['user_id']);
	$userId = $_SESSION['user_id'] ?? null;
	// Проверка наличия ID
	if (!isset($_GET['id'])) {
		echo "Товар не найден.";
		exit;
	}

	$categories = [];
	$query = $pdo->query("SELECT id, name FROM categories");
	if ($query) {
		$categories = $query->fetchAll(PDO::FETCH_ASSOC);
	}
	include 'login-component/login-modal.php';

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

	$id = $_GET['id'];

	// Запрос товара
	$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
	$stmt->execute([$id]);
	$product = $stmt->fetch();

	if (!$product) {
		echo "Товар не найден.";
		exit;
	}

	$price = (int)$product['price'];
	$discount = (int)$product['discount_percent'];

	if ($discount > 0) {
		$discountedPrice = round($price * (1 - $discount / 100));
	} else {
		$discountedPrice = $price;
	}

	$annualInterestRate = 18;
	$monthlyInterestMultiplier = 1 + ($annualInterestRate / 100);
	
	$credit = round(($discountedPrice * $monthlyInterestMultiplier) / 12);
	$installment = round($discountedPrice / 12);

	$stmt = $pdo->prepare("
		SELECT r.*, u.first_name 
		FROM reviews r
		JOIN users u ON r.user_id = u.id
		WHERE r.product_id = ?
		ORDER BY r.created_at DESC
	");
	$stmt->execute([$id]);
	$reviews = $stmt->fetchAll();

	$stmt = $pdo->prepare("SELECT COUNT(*) AS count, AVG(rating) AS avg_rating FROM reviews WHERE product_id = ?");
	$stmt->execute([$id]);
	$result = $stmt->fetch();

	$reviewsCount = $result['count'];
	$avgRating = round($result['avg_rating'], 1);

	function pluralForm($number, $forms) {
		$number = abs($number) % 100;
		$n1 = $number % 10;
		if ($number > 10 && $number < 20) return $forms[2];
		if ($n1 > 1 && $n1 < 5) return $forms[1];
		if ($n1 == 1) return $forms[0];
		return $forms[2];
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles/goodPage.css">
	<title><?= htmlspecialchars($product['name']) ?></title>
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
		<form action="search.html" class="search-container">
			<input type="text" class="search-input" placeholder="Поиск...">
			<button class="search-button" type="submit">
				<img src="img/icons/search.png" height="20" alt="Поиск">
			</button>
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
					<a href="cart.html" class="cart-link">
						<img src="img/icons/shopping-cart_white.png" height="30px" alt="Корзина">
						<span class="cart-counter"><?= count($cartItems) ?></span>
					</a>
					</div>
					<div class="account-logo">
					<a href="profile.html" class="account-link">
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
						<span class="cart-counter">0</span>
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
		<div class="good-info">
			<div class="name-rate">
				<h1 class="good-name"><?= htmlspecialchars($product['name']) ?></h1>
				<div class="good-rate">
					<div class="stars">
						<?php for ($i = 1; $i <= 5; $i++): ?>
							<img src="img/icons/<?php 
								if ($i <= floor($avgRating)) {
									echo 'star_yellow';
								} elseif ($i == floor($avgRating) + 1 && $avgRating - floor($avgRating) >= 0.5) {
									echo 'star_half';
								} else {
									echo 'star_gray';
								}
							?>.png" height="15px">
						<?php endfor; ?>
					</div>
					<a class="reviews-number"><?= $reviewsCount ?> <?= pluralForm($reviewsCount, ['отзыв', 'отзыва', 'отзывов']) ?></a>
				</div>
			</div>
			<div class="photo-info-price">
				<div class="good-photo">
					<img src="<?= $product['image_path'] ?>" height="520px">
				</div>
				<div class="good-attributes">
					<span>Характеристики</span>
					<div class="brand">
						<span>Производитель</span>
						<span class="dots"></span>
						<span class="brand value"><?= $product['brand'] ?></span>
					</div>
					<div class="color">
						<span>Цвет</span>
						<span class="dots"></span>
						<span class="color value"><?= $product['color'] ?></span>
					</div>
				</div>
				<div class="good-price">
					<div class="price">
						<span class="actual-price"><?= number_format($discountedPrice, 0, '', ' ') ?> р.</span>
						<?php if ($product['discount_percent'] > 0): ?>
							<span class="old-price"><?= number_format($price, 0, '', ' ') ?> р.</span>
						<?php else: ?>
							&nbsp;
						<?php endif; ?>
					</div>
					<div class="credit-installment">
						<span class="credit"><b>Кредит</b> от <?= number_format($credit, 0, '', ' ') ?> р/мес</span>
						<span class="credit"><b>Рассрочка</b> от <?= number_format($installment, 0, '', ' ') ?> р/мес</span>
					</div>
					<?php
						$isInCart = in_array($product['id'], $cartItems);
					?>
					<button class="add-to-cart-btn" data-id="<?= $product['id'] ?>"><?= $isInCart ? 'Убрать из корзины' : 'Добавить в корзину' ?></button>
				</div>
			</div>
		</div>
		<div class="reviews-description">
			<div class="buttons">
				<button class="description-btn active">Описание</button>
				<button class="reviews-btn">Отзывы</button>
			</div>
			<div class="container">
				<div class="description-container visible">
					<span class="description-text"><?= nl2br($product['description']) ?></span>
				</div>
				<div class="reviews-container">
					<?php foreach ($reviews as $review): ?>
						<div class="review-card">
							<div class="review-name-data">
								<span class="user-name"><?= htmlspecialchars($review['first_name']) ?></span>
								<span class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></span>
							</div>
							<div class="stars">
								<?php for ($i = 1; $i <= 5; $i++): ?>
									<img src="img/icons/<?= $i <= $review['rating'] ? 'star_yellow' : 'star_gray' ?>.png" height="15px">
								<?php endfor; ?>
							</div>
							<span class="review-text"><?= nl2br(htmlspecialchars($review['comment'])) ?></span>
						</div>
					<?php endforeach; ?>
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
	<script src="js/reviewsDescriptionSwitch.js"></script>
	<script src="js/showCategories.js"></script>
	<script src="js/reloadWindow.js"></script>
	<script src="js/actions.js"></script>
</body>
</html>