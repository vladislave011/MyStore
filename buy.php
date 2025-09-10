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

// Создание уникальной сессии для корзины
if (!isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = session_id();
}
$cart_session = $_SESSION['cart_session'];

// Получение товаров корзины
$cart_items = [];
$total_amount = 0;

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
    $total_amount += $row['price'] * $row['quantity'];
}
$stmt_items->close();

// Если корзина пуста, перенаправляем обратно
if (empty($cart_items)) {
    header("Location: index.php");
    exit();
}

// Обработка отправки формы заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Здесь будет код обработки заказа
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $comments = $_POST['comments'] ?? '';

    // В реальном приложении здесь будет:
    // 1. Валидация данных
    // 2. Сохранение заказа в БД
    // 3. Очистка корзины
    // 4. Перенаправление на страницу успеха

    // Для демонстрации просто очистим корзину
    $stmt_clear = $mysqli->prepare("DELETE FROM cart WHERE session_id=?");
    $stmt_clear->bind_param("s", $cart_session);
    $stmt_clear->execute();
    $stmt_clear->close();

    // Перенаправление на страницу успеха
    header("Location: order_success.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lynor - Оформление заказа</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="buy.css">
</head>
<body>
<!-- Header -->
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <h1 class="site-title">Lynor Shop<span>.</span></h1>
            <nav class="main-nav">
                <div class="nav-left">
                    <a href="index.php" class="nav-link">Главная</a>
                    <a href="index.php#products" class="nav-link">Товары</a>
                    <a href="index.php#about" class="nav-link">О нас</a>
                    <a href="index.php#contact" class="nav-link">Контакты</a>
                </div>
                <div class="nav-right">
                    <?php if (!$isLoggedIn): ?>
                        <a href="login.php" class="nav-link login-header-btn">Войти</a>
                    <?php else: ?>
                        <a href="profile.php" class="nav-link">Личный кабинет</a>
                        <a href="logout.php" class="nav-link login-header-btn">Выйти</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</header>

<!-- Checkout Section -->
<section class="checkout-section">
    <div class="container">
        <h2 class="section-title">Оформление заказа</h2>

        <div class="checkout-content">
            <div class="order-summary">
                <h3>Ваш заказ</h3>

                <div class="order-items">
                    <?php foreach($cart_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="assets/images/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="item-details">
                                <h4><?= htmlspecialchars($item['name']) ?></h4>
                                <div class="item-price"><?= number_format($item['price'], 0, '', ' ') ?>₸ × <?= $item['quantity'] ?></div>
                            </div>
                            <div class="item-total"><?= number_format($item['price'] * $item['quantity'], 0, '', ' ') ?>₸</div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-totals">
                    <div class="total-line">
                        <span>Промежуточный итог:</span>
                        <span><?= number_format($total_amount, 0, '', ' ') ?>₸</span>
                    </div>
                    <div class="total-line">
                        <span>Доставка:</span>
                        <span>Бесплатно</span>
                    </div>
                    <div class="total-line grand-total">
                        <span>Итого:</span>
                        <span><?= number_format($total_amount, 0, '', ' ') ?>₸</span>
                    </div>
                </div>
            </div>

            <div class="checkout-form">
                <h3>Данные для доставки</h3>

                <form method="POST" id="orderForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">ФИО *</label>
                            <input type="text" id="name" name="name" required
                                   value="<?= $isLoggedIn ? ($user['name'] ?? '') : '' ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required
                                   value="<?= $isLoggedIn ? ($user['email'] ?? '') : '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Телефон *</label>
                            <input type="tel" id="phone" name="phone" required
                                   value="<?= $isLoggedIn ? ($user['phone'] ?? '') : '' ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">Адрес доставки *</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">Город *</label>
                            <input type="text" id="city" name="city" required value="Алматы">
                        </div>
                        <div class="form-group">
                            <label for="postcode">Почтовый индекс</label>
                            <input type="text" id="postcode" name="postcode">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="payment_method">Способ оплаты *</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="">Выберите способ оплаты</option>
                                <option value="cash">Наличными при получении</option>
                                <option value="card">Банковской картой онлайн</option>
                                <option value="transfer">Банковский перевод</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="comments">Комментарий к заказу</label>
                            <textarea id="comments" name="comments" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-submit">
                        <button type="submit" class="submit-order-btn">Подтвердить заказ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="main-footer">
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
                    <li><a href="index.php#products">Мужская обувь</a></li>
                    <li><a href="index.php#products">Женская обувь</a></li>
                    <li><a href="index.php#products">Новая коллекция</a></li>
                    <li><a href="index.php#products">Распродажа</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3 class="footer-title">Информация</h3>
                <ul>
                    <li><a href="index.php#about">О нас</a></li>
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

<script>
    // Простая валидация формы
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = this.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = 'red';
            } else {
                field.style.borderColor = '';
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Пожалуйста, заполните все обязательные поля (отмечены *)');
        }
    });
</script>
</body>
</html>