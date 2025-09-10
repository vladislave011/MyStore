<?php
// Включаем ошибки только на dev (потом убрать!)
global $mysqli;
ini_set('display_errors', 1);
error_reporting(E_ALL);



// Загружаем db.php (там session_start и подключение к БД)
require_once 'db.php';

// Пользователь из сессии
$user = $_SESSION['user'] ?? null;
$isLoggedIn = !empty($user);


// Логирование (не выводим в HTML!)
error_log("Session ID: " . session_id());
error_log("User session: " . print_r($user ?? 'No user', true));
error_log("Is logged in: " . ($isLoggedIn ? 'Yes' : 'No'));


// Создание уникальной сессии для корзины
if (!isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = session_id();
}
$cart_session = $_SESSION['cart_session'];

// Очистка корзины
if (isset($_POST['clear_cart'])) {
    $stmt_clear = $mysqli->prepare("DELETE FROM cart WHERE session_id=?");
    $stmt_clear->bind_param("s", $cart_session);
    $stmt_clear->execute();
    $stmt_clear->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Получение всех товаров и категорий
$products = [];
$categories = [];
if ($result = $mysqli->query("SELECT * FROM products")) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
        $catName = $row['category'] ?? 'Все';
        if (!in_array($catName, $categories)) {
            $categories[] = $catName;
        }
    }
    $result->free();
}

// Подсчет товаров в корзине
$cart_count = 0;
$stmt_cart = $mysqli->prepare("SELECT SUM(quantity) FROM cart WHERE session_id=?");
$stmt_cart->bind_param("s", $cart_session);
$stmt_cart->execute();
$stmt_cart->bind_result($cart_count);
$stmt_cart->fetch();
$stmt_cart->close();
$cart_count = $cart_count ?? 0;

// Получение товаров корзины
$cart_items = [];
$stmt_items = $mysqli->prepare("
    SELECT c.id, p.name, p.price, c.quantity, p.image
    FROM cart c
    JOIN products p ON c.product_id=p.id
    WHERE c.session_id=?
");
$stmt_items->bind_param("s", $cart_session);
$stmt_items->execute();
$res_items = $stmt_items->get_result();
while ($row = $res_items->fetch_assoc()) {
    $cart_items[] = $row;
}
$stmt_items->close();
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lynor - Магазин кроссовок</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
</head>
<body>
<!-- Header -->
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <h1 class="site-title">Lynor Shop<span>.</span></h1>
            <nav class="main-nav">
                <div class="nav-left">
                    <a href="#" class="nav-link">Главная</a>
                    <a href="#products" class="nav-link">Товары</a>
                    <a href="#about" class="nav-link">О нас</a>
                    <a href="#contact" class="nav-link">Контакты</a>
                </div>
                <div class="nav-right">
                    <?php if (!$isLoggedIn): ?>
                        <!-- Если не авторизован -->
                        <a href="login.php" class="nav-link login-header-btn">Войти</a>
                    <?php else: ?>
                        <!-- Если авторизован -->
                        <a href="profile.php" class="nav-link">Личный кабинет</a>
                        <a href="logout.php" class="nav-link login-header-btn">Выйти</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</header>

<!-- ... остальной код без изменений ... -->

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Lynor Shop</h1>
            <p class="hero-subtitle">Самые лучшие цены и качество в Алматы </p>
            <button class="hero-btn" id="startShoppingBtn">Начать покупки</button>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section" id="products">
    <div class="container">
        <h2 class="section-title text-center">Категории</h2>
        <div class="categories-container">
            <button class="category-btn active" data-category="all">Популярное</button>
            <button class="category-btn" data-category="Nike">Nike</button>
            <button class="category-btn" data-category="Asics">Asics</button>
            <button class="category-btn" data-category="Adidas">Adidas</button>
            <button class="category-btn" data-category="Kappa">Kappa</button>
            <button class="category-btn" data-category="Loro Piana">Loro Piana</button>
        </div>
    </div>
</section>

<!-- Products Section -->
<!-- Products Section -->
<section class="products-section bg-light">
    <div class="container">
        <h2 class="section-title text-center">Наши самые популярные модели</h2>
        <div class="products-grid">
            <?php
            $images = ['picture1.jpg', 'picture2.jpg', 'picture3.jpg','picture4.jpg', 'picture5.jpg', 'picture6.jpg','picture7.jpg', 'picture8.jpg', 'picture9.jpg'];
            $i = 0;
            foreach($products as $product):
                ?>
                <div class="product-card animate delay-<?= ($i % 5) + 1 ?>" data-category="<?= htmlspecialchars(isset($product['category']) ? $product['category'] : 'Все') ?>">
                    <?php if(isset($product['featured']) && $product['featured']): ?>
                        <span class="product-badge">Хит продаж</span>
                    <?php endif; ?>
                    <img src="assets/images/<?= $images[$i % count($images)] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-description"><?= isset($product['description']) ? htmlspecialchars($product['description']) : 'Стильные и удобные кроссовки для повседневной носки.' ?></p>
                        <div class="product-price"><?= number_format($product['price'], 0, '', ' ') ?>₸</div>
                        <div class="product-actions">
                            <!-- Выбор размера на карточке -->
                            <div class="size-selection-card">
                                <label for="sizeSelect_<?= $product['id'] ?>">Размер:</label>
                                <select id="sizeSelect_<?= $product['id'] ?>" class="size-select">
                                    <?php for($s = 36; $s <= 44; $s++): ?>
                                        <option value="<?= $s ?>" <?= $s == 36 ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <button type="button" class="add-to-cart-btn" data-product-id="<?= $product['id'] ?>">
                                <span>В корзину</span>
                            </button>
                            <button type="button" class="view-details-btn"  data-product='<?= htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8') ?>'>
                                Подробнее
                            </button>
                        </div>
                    </div>
                </div>
                <?php
                $i++;
            endforeach;
            ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title text-center">Почему выбирают нас</h2>
        <div class="features-grid">
            <div class="feature-card slide-in delay-1">
                <div class="feature-icon">🚚</div>
                <h3 class="feature-title">Бесплатная доставка</h3>
                <p class="feature-description">Бесплатная доставка по всему Казахстану при заказе от 20 000₸</p>
            </div>
            <div class="feature-card slide-in delay-2">
                <div class="feature-icon">↩️</div>
                <h3 class="feature-title">Возврат 30 дней</h3>
                <p class="feature-description">Легкий возврат в течение 30 дней после покупки</p>
            </div>
            <div class="feature-card slide-in delay-3">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Безопасная оплата</h3>
                <p class="feature-description">Безопасные способы оплаты с шифрованием данных</p>
            </div>
            <div class="feature-card slide-in delay-4">
                <div class="feature-icon">☎️</div>
                <h3 class="feature-title">Поддержка 24/7</h3>
                <p class="feature-description">Наша служба поддержки готова помочь в любое время</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section mt-50">
    <div class="container">
        <h2 class="newsletter-title">Подпишитесь на рассылку</h2>
        <p class="hero-subtitle">Узнавайте первыми о новых коллекциях и эксклюзивных предложениях</p>
        <form class="newsletter-form mt-30">
            <label>
                <input type="email" class="newsletter-input" placeholder="Ваш email" required>
            </label>
            <button type="submit" class="newsletter-btn">Подписаться</button>
        </form>
    </div>
</section>

<!-- Footer -->
<footer class="main-footer" id="contact">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-info">
                <h3 class="footer-title">Lynor</h3>
                <p>Мы предлагаем стильную и качественную обувь для тех, кто ценит комфорт и современный дизайн.</p>
                <div class="social-links">
                    <a href="#" class="social-link">FB</a>
                    <a href="#" class="social-link">IG</a>
                    <a href="#" class="social-link">TW</a>
                    <a href="#" class="social-link">YT</a>
                </div>
            </div>
            <div class="footer-links">
                <h3 class="footer-title">Магазин</h3>
                <ul>
                    <li><a href="#">Мужская обувь</a></li>
                    <li><a href="#">Женская обувь</a></li>
                    <li><a href="#">Новая коллекция</a></li>
                    <li><a href="#">Распродажа</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3 class="footer-title">Информация</h3>
                <ul>
                    <li><a href="#">О нас</a></li>
                    <li><a href="#">Доставка и оплата</a></li>
                    <li><a href="#">Условия возврата</a></li>
                    <li><a href="#">Политика конфиденциальности</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h3 class="footer-title">Контакты</h3>
                <p>Алматы, пр. Абая 777</p>
                <p>+7 (777) 777-77-77</p>
                <p>lynor@lynor.kz</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Lynor. Все права защищены.</p>
        </div>
    </div>
</footer>

<!-- Cart Modal -->
<div class="modal" id="cartModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ваша корзина</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div id="cartItems">
            <?php if(empty($cart_items)): ?>
                <p class="text-center">Ваша корзина пуста</p>
            <?php else: ?>
                <?php foreach($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="assets/images/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-image">
                        <div class="cart-item-info">
                            <h4 class="cart-item-name"><?= htmlspecialchars($item['name']) ?></h4>
                            <div class="cart-item-price"><?= number_format($item['price'], 0, '', ' ') ?>₸ × <?= $item['quantity'] ?> шт.</div>
                            <div class="cart-item-total"><?= number_format($item['price'] * $item['quantity'], 0, '', ' ') ?>₸</div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="cart-total">
                    <span>Итого:</span>
                    <span>
                        <?php
                        $total = 0;
                        foreach($cart_items as $item) {
                            $total += $item['price'] * $item['quantity'];
                        }
                        echo number_format($total, 0, '', ' ') . '₸';
                        ?>
                    </span>
                </div>
                <div class="cart-actions">
                    <form method="POST">
                        <button type="submit" name="clear_cart" class="btn btn-secondary">Очистить корзину</button>
                    </form>
                    <a href="buy.php" class="btn btn-primary">Оформить заказ</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Product Detail Modal -->
<div class="modal" id="productDetailModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Описание товара</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <img id="detailProductImage" src="" alt="" class="product-detail-image">
            <h3 id="detailProductName"></h3>
            <p id="detailProductDescription"></p>
            <div class="product-detail-price" id="detailProductPrice"></div>

            <!-- Добавлен выбор размера -->
            <div class="size-selection">
                <h4>Выберите размер:</h4>
                <div class="size-options">
                    <?php for($i = 36; $i <= 44; $i++): ?>
                        <label class="size-option">
                            <input type="radio" name="productSize" value="<?= $i ?>" <?= $i == 36 ? 'checked' : '' ?>>
                            <span><?= $i ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>

            <button class="add-to-cart-btn product-detail-btn" id="detailProductAddBtn">Добавить в корзину</button>
        </div>
    </div>
</div>

<!-- Floating Cart -->
<div class="floating-cart" id="floatingCart">
    <span>🛒</span>
    <span class="cart-count"><?= $cart_count ?></span>
</div>

<script>
    // Статус авторизации из PHP
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
</script>

<!-- Подключение JavaScript файла -->
<script src="index.js"></script>
</body>
</html