<?php
include 'server/db/db.php';
include 'login-component/login-modal.php';
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;

$categoryId = $_GET['category'] ?? null;

if ($categoryId) {
    // Запрос товаров только из выбранной категории
    $productStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
    $productStmt->execute([$categoryId]);
} else {
    // Если категория не выбрана, показываем все товары
    $productStmt = $pdo->query("SELECT * FROM products");
}
$products = $productStmt->fetchAll(); // ✅ Сохраняем здесь, до перезаписи
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


$sort = $_GET['sort'] ?? null;

$orderBy = '';
switch ($sort) {
	case 'cheaper':
		$orderBy = 'p.price ASC';
		break;
	case 'expensive':
		$orderBy = 'p.price DESC';
		break;
	case 'high_rate':
		$orderBy = 'avg_rating DESC';
		break;
	case 'low_rate':
		$orderBy = 'avg_rating ASC';
		break;
	default:
		$orderBy = 'p.id DESC'; // по умолчанию – новые товары
}

$params = [];
$query = "
	SELECT 
		p.*, 
		AVG(r.rating) AS avg_rating 
	FROM products p
	LEFT JOIN reviews r ON p.id = r.product_id
";

// фильтр по категории
if ($categoryId) {
	$query .= " WHERE p.category_id = ?";
	$params[] = $categoryId;
}

$query .= " GROUP BY p.id ORDER BY $orderBy";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categoryName = 'Все товары'; // Значение по умолчанию

if ($categoryId) {
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();
    if ($category) {
        $categoryName = $category['name'];
    }
}


$where = [];
$params = [];

if ($categoryId) {
    $where[] = 'category_id = ?';
    $params[] = $categoryId;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Уникальные бренды
$brandStmt = $pdo->prepare("SELECT DISTINCT brand FROM products $whereSQL");
$brandStmt->execute($params);
$availableBrands = $brandStmt->fetchAll(PDO::FETCH_COLUMN);

// Уникальные цвета
$colorStmt = $pdo->prepare("SELECT DISTINCT color FROM products $whereSQL");
$colorStmt->execute($params);
$availableColors = $colorStmt->fetchAll(PDO::FETCH_COLUMN);

$categoryId = $_GET['category'] ?? null;
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
$brand = $_GET['brand'] ?? null;
$color = $_GET['color'] ?? null;
$discountOnly = isset($_GET['discount_only']);

$sql = "SELECT * FROM products WHERE 1=1";

$params = [];
$sql = "
    SELECT 
        p.*, 
        AVG(r.rating) AS avg_rating 
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE 1=1
";

// Категория
if ($categoryId) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryId;
}

// Цена
if (!empty($minPrice)) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice;
}
if (!empty($maxPrice)) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

// Бренд
if ($brand && $brand !== 'all') {
    $sql .= " AND p.brand = ?";
    $params[] = $brand;
}

// Цвет
if ($color && $color !== 'all') {
    $sql .= " AND p.color = ?";
    $params[] = $color;
}

// Скидка
if ($discountOnly) {
    $sql .= " AND p.discount_percent > 0";
}

// Группировка и сортировка
$sql .= " GROUP BY p.id ORDER BY $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();



?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles/catalog.css">
	<link rel="stylesheet" href="login-component/loginStyle.css">
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
		<form method="GET" action="catalog.php" id="filterForm">
			<input type="hidden" name="category" value="<?= htmlspecialchars($categoryId) ?>">
			<input type="hidden" name="sort" id="sortHiddenInput" value="<?= htmlspecialchars($sort ?? '') ?>">
			<div class="filter">
				<div class="price">
					<span class="price-span">Цена</span>
					<div class="price-inputs">
						<input type="number" name="min_price" class="price-min" placeholder="От" min="0" value="<?= $_GET['min_price'] ?? '' ?>">
						<span class="price-separator">—</span>
						<input type="number" name="max_price" class="price-max" placeholder="До" min="0" value="<?= $_GET['max_price'] ?? '' ?>">
					</div>
				</div>
				<div class="manufacturer">
					<span>Бренд</span>
					<select name="brand" class="manufacturer-select">
						<option value="all" <?= ($_GET['brand'] ?? '') === 'all' ? 'selected' : '' ?>>Все</option>
						<?php foreach ($availableBrands as $brand): ?>
							<option value="<?= htmlspecialchars($brand) ?>" <?= ($_GET['brand'] ?? '') === $brand ? 'selected' : '' ?>>
								<?= htmlspecialchars($brand) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>


				<div class="color">
					<span>Цвет</span>
					<select name="color" class="color-select">
						<option value="all" <?= ($_GET['color'] ?? '') === 'all' ? 'selected' : '' ?>>Любой</option>
						<?php foreach ($availableColors as $color): ?>
							<option value="<?= htmlspecialchars($color) ?>" <?= ($_GET['color'] ?? '') === $color ? 'selected' : '' ?>>
								<?= htmlspecialchars($color) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>


				<div class="filter-discount">
					<label>
						<input type="checkbox" name="discount_only" <?= isset($_GET['discount_only']) ? 'checked' : '' ?>> Товары со скидкой
					</label>
				</div>

				<button type="submit">Применить</button>
				<button type="button" id="resetFilters">Сбросить</button>
			</div>
		</form>

		<div class="catalog-page">
			<h1 class="favorites-span"><?= htmlspecialchars($categoryName) ?></h1>
			<div class="sort">
				<span>Сортировка:</span>
				<select id="sortSelect" name="sort" class="sort-select">
					<option value="new" <?= empty($sort) ? 'selected' : '' ?>>Новые</option>
					<option value="cheaper" <?= $sort === 'cheaper' ? 'selected' : '' ?>>Дешевле</option>
					<option value="expensive" <?= $sort === 'expensive' ? 'selected' : '' ?>>Дороже</option>
					<option value="high_rate" <?= $sort === 'high_rate' ? 'selected' : '' ?>>Оценка выше</option>
					<option value="low_rate" <?= $sort === 'low_rate' ? 'selected' : '' ?>>Оценка ниже</option>
				</select>
			</div>
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
								<button type="button" class="add-to-cart" data-id="<?= $product['id'] ?>" <?= !$isLoggedIn ? 'onclick="openLoginModal(event)"' : '' ?>>
									<img src="img/icons/<?= $isInCart ? 'shopping-cart_green' : 'shopping-cart_black' ?>.png" height="30px">
								</button>
								<button type="button" class="add-to-favorites" data-id="<?= $product['id'] ?>" <?= !$isLoggedIn ? 'onclick="openLoginModal(event)"' : '' ?>>
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
	<script src="js/actions.js"></script>
	<script src="js/sort.js"></script>
	<script src="js/filter.js"></script>
	<script src="js/search.js"></script>
	<script src="js/showCategories.js"></script>

</body>
</html>