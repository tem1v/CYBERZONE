<?php
	session_start();
	include 'server/db/db.php';

	$userId = $_SESSION['user_id'] ?? null;

	if (!$userId) {
		header('Location: login.php');
		exit;
	}

	$stmt = $pdo->prepare("
		SELECT p.*
		FROM cart_items c
		JOIN products p ON c.product_id = p.id
		WHERE c.user_id = ?
	");
	$stmt->execute([$userId]);
	$cartProducts = $stmt->fetchAll();

	$stmt = $pdo->prepare("
		SELECT p.*
		FROM favorites c
		JOIN products p ON c.product_id = p.id
		WHERE c.user_id = ?
	");
	$stmt->execute([$userId]);
	$favoriteProducts = $stmt->fetchAll();

	function pluralForm($number, $forms) {
		$number = abs($number) % 100;
		$n1 = $number % 10;
		if ($number > 10 && $number < 20) return $forms[2];
		if ($n1 > 1 && $n1 < 5) return $forms[1];
		if ($n1 == 1) return $forms[0];
		return $forms[2];
	}
	$totalPrice = 0;        // сумма всех оригинальных цен
	$totalDiscount = 0;     // сумма всех скидок

	foreach ($cartProducts as $product) {
		$original = $product['price'];
		$discountPercent = $product['discount_percent'];

		$discountAmount = ($original * $discountPercent) / 100;
		$discounted = $original - $discountAmount;

		$totalPrice += $original;
		$totalDiscount += $discountAmount;
	}

	$finalPrice = $totalPrice - $totalDiscount;

	
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles/cart.css">
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
						<span class="cart-counter">1</span>
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
		<div class="cart-page">
			<?php if (empty($cartProducts)): ?>
				<div class="empty-cart-message">
					<h1>Ваша корзина пуста.</h1>
				</div>
			<?php else: ?>
				<div class="cart-list">
					<?php foreach ($cartProducts as $product): ?>
						<div class="cart-card">
							<a href="goodPage.php?id=<?= $product['id'] ?>">
								<img src="<?= htmlspecialchars($product['image_path']) ?>" height="220px">
							</a>
							<span class="good-name"><?= htmlspecialchars($product['name']) ?></span>
							<div class="cart-good-price">
								<span class="card-actual-price">
									<?= number_format(
										!empty($product['discount_percent']) 
											? $product['price'] * (1 - $product['discount_percent'] / 100) 
											: $product['price'], 
										0, '', ' '
									) ?> р.
								</span>

								<span class="card-old-price" style="<?= empty($product['discount_percent']) ? 'text-decoration: none; color: transparent;' : '' ?>">
									<?php if (!empty($product['discount_percent'])): ?>
										<?= number_format($product['price'], 0, '', ' ') ?> р.
									<?php else: ?>
										&nbsp;
									<?php endif; ?>
								</span>
							</div>
							<?php
								$cartItems = array_column($cartProducts, 'id');
								$favItems = array_column($favoriteProducts, 'id');

								$isInCart = in_array($product['id'], $cartItems);
								$isInFav = in_array($product['id'], $favItems);
							?>
							<div class="card-buttons">
								<button class="remove-from-cart" data-id="<?= $product['id'] ?>">
									<img src="img/icons/recycle-bin.png" height="30px">
								</button>
								<button class="add-to-favorites" data-id="<?= $product['id'] ?>">
									<img src="img/icons/<?= $isInFav ? 'heart_red' : 'heart_black' ?>.png" height="30px">
								</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="make-order">
					<div class="bill">
						<div class="goods-price">
							<span class="goods-price counter"><?= count($cartProducts) ?> <?= pluralForm(count($cartProducts), ['товар', 'товара', 'товаров']) ?></span>
							<span class="dots"></span>
							<span class="goods-price price"><?= number_format($totalPrice, 0, '', ' ') ?> р.</span>
						</div>
						<div class="discound">
							<span class="discound counter">Скидка</span>
							<span class="dots"></span>
							<span class="discound price"><?= number_format($totalDiscount, 0, '', ' ') ?> р.</span>
						</div>
						<div class="final-price">
							<span class="final-price span">Итого</span>
							<span class="dots"></span>
							<span class="final-price price"><?= number_format($finalPrice, 0, '', ' ') ?> р.</span>
						</div>
					</div>
					<button class="make-order-btn">Оформить заказ</button>
				</div>
			<?php endif; ?>

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
	<script src="js/deleteFromCart.js"></script>
	<script src="js/makeOrder.js"></script>
	<script src="js/search.js"></script>
	<script src="js/showCategories.js"></script>
</body>
</html>