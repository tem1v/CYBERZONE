<?php
session_start();
include 'server/db/db.php';

$searchResults = [];
$cartItems = [];
$favItems = [];

$searchQuery = trim($_GET['q'] ?? '');

if ($searchQuery === '') {
    $error = "Введите поисковый запрос.";
} else {
    // Поиск товаров по имени или категории
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.name LIKE :query OR c.name LIKE :query
    ");
    $stmt->execute(['query' => "%$searchQuery%"]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем товары в корзине пользователя
    if (!empty($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        $cartStmt = $pdo->prepare("SELECT product_id FROM cart_items WHERE user_id = ?");
        $cartStmt->execute([$userId]);
        $cartItems = $cartStmt->fetchAll(PDO::FETCH_COLUMN);

        // Получаем избранные товары
        $favStmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
        $favStmt->execute([$userId]);
        $favItems = $favStmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles/search-discount.css">
	<title>Cyberzone</title>
	<link rel="shortcut icon" href="img/logo/cyberzone_icon.png">
</head>
<body>
	<header class="header-container">
		<div class="logo">
			<a href="mainPage.html">
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
				<a href="discount.html">Скидки</a>
				<div class="category-drop-down">
					<span>Категории</span>
					<div class="dropdown-menu">
						<a href="catalog.html" class="dropdown-item">Мыши</a>
						<a href="catalog.html" class="dropdown-item">Клавиатуры</a>
						<a href="catalog.html" class="dropdown-item">Наушники</a>
						<a href="catalog.html" class="dropdown-item">Кресла</a>
						<a href="catalog.html" class="dropdown-item">Мониторы</a>
					</div>
				</div>
			</div>
			<div class="action-icons">
				<div class="favorites-logo">
					<a href="favorites.html">
						<img src="img/icons/heart_white.png" height="30px">
					</a>
				</div>
				<div class="cart-logo">
					<a href="cart.html"" class="cart-link">
						<img src="img/icons/shopping-cart_white.png" height="30px" alt="Корзина">
						<span class="cart-counter">1</span>
					</a>
				</div>
				<div class="account-logo">
					<a href="profile.html" class="account-link">
						<img src="img/icons/user.png" height="30px" alt="Аккаунт">
						<span class="account-name">Артём</span>
					</a>
				</div>
			</div>
		</nav>
		
	</header>



	<main>
		<h2 class="favorites-span">Результаты поиска по запросу: "<?= htmlspecialchars($searchQuery) ?>"</h2>
		<div class="catalog">
		<?php foreach ($searchResults as $product): ?>
	<?php
		$discountedPrice = $product['price'] * (1 - $product['discount_percent'] / 100);
		$isInCart = in_array($product['id'], $cartItems ?? []);
		$isInFav = in_array($product['id'], $favItems ?? []);
	?>
	<div class="card" data-id="<?= $product['id'] ?>">
		<a href="goodPage.php?id=<?= $product['id'] ?>">
			<img src="<?= $product['image_path'] ?>" height="220px">
			<span class="card-good-name"><?= htmlspecialchars($product['name']) ?></span>
		</a>

		<div class="card-price-buttons">
			<div class="card-price">
				<span class="card-actual-price"><?= number_format($discountedPrice, 0, '', ' ') ?> р.</span>
				<?php if ($product['discount_percent'] > 0): ?>
					<span class="card-old-price"><?= number_format($product['price'], 0, '', ' ') ?> р.</span>
				<?php else: ?>
					&nbsp;
				<?php endif; ?>
			</div>

			<div class="card-buttons">
				<button type="button" class="add-to-cart" data-id="<?= $product['id'] ?>">
					<img src="img/icons/<?= $isInCart ? 'shopping-cart_green' : 'shopping-cart_black' ?>.png" height="30px">
				</button>
				<button type="button" class="add-to-favorites" data-id="<?= $product['id'] ?>">
					<img src="img/icons/<?= $isInFav ? 'heart_red' : 'heart_black' ?>.png" height="30px">
				</button>
			</div>
		</div>

		<?php if ($product['discount_percent'] > 0): ?>
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