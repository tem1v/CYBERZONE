<?php
	include 'server/db/db.php';
    session_start();
	$isLoggedIn = isset($_SESSION['user_id']);
	$userId = $_SESSION['user_id'] ?? null;
	$products = [];

	// if ($query = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 12")) {
    // 	$products = $query->fetchAll(PDO::FETCH_ASSOC);
	// } else {
	// 	print_r($pdo->errorInfo());
	// }
	
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


	$stmt = $pdo->query("SELECT * FROM products WHERE discount_percent > 0");
	$discountedProducts = $stmt->fetchAll();

	// Если товаров нет, ничего не делаем
	if (count($discountedProducts) === 0) {
		$dailyProduct = null;
	} else {
		$today = date('Y-m-d');
		$seed = strtotime($today);
		srand($seed);

		$index = rand(0, count($discountedProducts) - 1);
		$dailyProduct = $discountedProducts[$index];
	}


	$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    // Получаем любимые категории
    $stmt = $pdo->prepare("
        SELECT c.id
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        WHERE f.user_id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$userId]);
    $favCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($favCategories)) {
        // Формируем плейсхолдеры
        $placeholders = implode(',', array_fill(0, count($favCategories), '?'));

        // Товары из любимых категорий
        $stmt1 = $pdo->prepare("
            SELECT p.*, COUNT(f.product_id) AS fav_count
            FROM products p
            LEFT JOIN favorites f ON p.id = f.product_id
            WHERE p.category_id IN ($placeholders)
            GROUP BY p.id
            ORDER BY fav_count DESC
        ");
        $stmt1->execute($favCategories);
        $preferredProducts = $stmt1->fetchAll();

        // Товары из остальных категорий
        $stmt2 = $pdo->prepare("
            SELECT p.*, COUNT(f.product_id) AS fav_count
            FROM products p
            LEFT JOIN favorites f ON p.id = f.product_id
            WHERE p.category_id NOT IN ($placeholders)
            GROUP BY p.id
            ORDER BY fav_count DESC
        ");
        $stmt2->execute($favCategories);
        $otherProducts = $stmt2->fetchAll();

        // Объединяем: сначала избранные категории, потом остальные
        $products = array_merge($preferredProducts, $otherProducts);
    } else {
        // У пользователя нет избранного — показываем по популярности
        $stmt = $pdo->query("
            SELECT p.*, COUNT(f.product_id) AS fav_count
            FROM products p
            LEFT JOIN favorites f ON p.id = f.product_id
            GROUP BY p.id
            ORDER BY fav_count DESC
        ");
        $products = $stmt->fetchAll();
    }
} else {
    // Неавторизованный — просто по популярности
    $stmt = $pdo->query("
        SELECT p.*, COUNT(f.product_id) AS fav_count
        FROM products p
        LEFT JOIN favorites f ON p.id = f.product_id
        GROUP BY p.id
        ORDER BY fav_count DESC
    ");
    $products = $stmt->fetchAll();
}

	

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles/mainPage.css">
	<link rel="stylesheet" href="login-component/loginStyle.css">
	<title>Cyberzone</title>
	<link rel="shortcut icon" href="img/logo/cyberzone_icon.png">
</head>
<body>
	<header class="header-container">
		<div class="logo">
			<a href="#">
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
		<div class="banner-and-daily-goods">
			<div class="banner">
				<a href="catalog.php" id="bannerLink"><img src="img/banners/banner_3.png" class="banner-img" height="350px" alt="Баннер"></a>
				<button class="banner-btn banner-btn-left">
					<
				</button>
				<button class="banner-btn banner-btn-right">
					>
				</button>
			</div>
			<?php if ($dailyProduct): ?>
				<div class="daily-good">
					<span class="daily-good-span">Товар дня</span>
					<a href="goodPage.php?id=<?= $dailyProduct['id'] ?>">
						<img src="<?= $dailyProduct['image_path'] ?>" height="220" class="daily-good-photo">
					</a>
					<div class="daily-good-description-cart">
						<a href="goodPage.php?id=<?= $dailyProduct['id'] ?>" class="daily-good-description">
							<span class="daily-good-name"><?= htmlspecialchars($dailyProduct['name']) ?></span>
							<div class="price">
								<span class="actual-price"><?= number_format($dailyProduct['price'] * (1 - $dailyProduct['discount_percent'] / 100), 0, '', ' ') ?> р.</span>
								<span class="old-price"><?= number_format($dailyProduct['price'], 0, '', ' ') ?> р.</span>
							</div>
						</a>
						<?php
							$isInCart = in_array($dailyProduct['id'], $cartItems);
						?>
						<button class="add-to-cart" data-id="<?= $dailyProduct['id'] ?>" <?= !$isLoggedIn ? 'onclick="openLoginModal(event)"' : '' ?>>
							<img src="img/icons/<?= $isInCart ? 'shopping-cart_green' : 'shopping-cart_black' ?>.png" height="30px">
						</button>
					</div>
				</div>

			<?php endif; ?>
		</div>
		<h1>Рекомендуемое</h1>
		<div class="catalog">
		<?php foreach ($products as $product): ?>
			<div href="goodPage.php?id=<?= $product['id'] ?>" class="card">
				<a href="goodPage.php?id=<?= $product['id'] ?>">
					<img src="<?= $product['image_path'] ?>" height="220px">
					<span class="card-good-name"><?= htmlspecialchars($product['name']) ?></span>
				</a>

				
				<div class="card-price-buttons">
					<div class="card-price">
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
						$isInCart = in_array($product['id'], $cartItems);
						$isInFav = in_array($product['id'], $favItems);
					?>
					<div class="card-buttons">
						<button
							type="button"
							class="add-to-cart"
							data-id="<?= $product['id'] ?>"
							<?= !$isLoggedIn ? 'onclick="openLoginModal(event)"' : '' ?>
						>
							<img src="img/icons/<?= $isInCart ? 'shopping-cart_green' : 'shopping-cart_black' ?>.png" height="30px">
						</button>

						<button
							type="button"
							class="add-to-favorites"
							data-id="<?= $product['id'] ?>"
							<?= !$isLoggedIn ? 'onclick="openLoginModal(event)"' : '' ?>
						>
							<img src="img/icons/<?= $isInFav ? 'heart_red' : 'heart_black' ?>.png" height="30px">
						</button>
					</div>
				</div>
				<?php if (!empty($product['discount_percent'])): ?>
					<span class="card-discount">-<?= $product['discount_percent'] ?>%</span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
			
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
	<script src="js/search.js"></script>
	<script src="js/mainPageSlider.js"></script>
	<script src="js/showCategories.js"></script>
	<script src="js/actions.js"></script>
</body>
</html>