<?php
// profile.php

session_start();

require_once 'db.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Получаем дополнительные данные пользователя (заглушки для демонстрации)
$user_orders = [
        ['id' => '#12345', 'date' => '2023-05-15', 'status' => 'Доставлен', 'total' => '52000 тг.', 'items' => 2],
        ['id' => '#12346', 'date' => '2023-06-22', 'status' => 'В обработке', 'total' => '28990 тг.', 'items' => 1],
        ['id' => '#12347', 'date' => '2023-07-10', 'status' => 'Отправлен', 'total' => '115890 тг.', 'items' => 3]
];

$user_favorites = [
        ['id' => 1, 'name' => 'Nike Air Max 270', 'price' => '28 500 тг.', 'image' => '/assets/images/picture1.jpg'],
        ['id' => 2, 'name' => 'Adidas Ultraboost 22', 'price' => '29 900 тг.', 'image' => '/assets/images/picture2.jpg'],
        ['id' => 3, 'name' => 'New Balance 550', 'price' => '24 500 тг.', 'image' => '/assets/images/picture4.jpg'],
        ['id' => 4, 'name' => 'Puma RS-X', 'price' => '22 500 тг', 'image' => '/assets/images/picture5.jpg']
];

$user_addresses = [
        ['id' => 1, 'title' => 'Дом', 'address' => 'ул. Примерная, д. 10, кв. 25', 'is_default' => true],
        ['id' => 2, 'title' => 'Работа', 'address' => 'ул. Рабочая, д. 5, офис 304', 'is_default' => false]
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - Lynor Sneakers</title>
    <link rel="stylesheet" href="profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="user-panel">
    <div class="user-info">
        <img src="<?php echo $user['picture']; ?>" alt="Аватар" class="user-avatar">
        <span class="user-name"><?php echo $user['name']; ?></span>
    </div>
    <div class="user-actions">
        <a href="index.php" class="btn-secondary"><i class="fas fa-home"></i> На главную</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Выйти</a>
    </div>
</div>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="<?php echo $user['picture']; ?>" alt="Аватар" class="profile-avatar">
            <h2><?php echo $user['name']; ?></h2>
            <p>Участник с <?php echo date('d.m.Y', strtotime($user['created_at'] ?? '2023-01-01')); ?></p>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active" data-target="overview">
                    <i class="fas fa-th-large"></i>
                    <span>Обзор</span>
                </li>
                <li class="nav-item" data-target="orders">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Мои заказы</span>
                </li>
                <li class="nav-item" data-target="favorites">
                    <i class="fas fa-heart"></i>
                    <span>Избранное</span>
                </li>
                <li class="nav-item" data-target="addresses">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Адреса доставки</span>
                </li>
                <li class="nav-item" data-target="settings">
                    <i class="fas fa-cog"></i>
                    <span>Настройки</span>
                </li>
                <li class="nav-item" data-target="loyalty">
                    <i class="fas fa-award"></i>
                    <span>Программа лояльности</span>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <div class="loyalty-card">
                <div class="loyalty-points">
                    <span class="points">1 245</span>
                    <span>баллов</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: 45%"></div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <section id="overview" class="content-section active">
            <div class="section-header">
                <h1>Обзор аккаунта</h1>
                <p>Добро пожаловать в ваш личный кабинет, <?php echo $user['name']; ?>!</p>
            </div>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3>5</h3>
                        <p>Всего заказов</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>12</h3>
                        <p>В избранном</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="stat-info">
                        <h3>1 245</h3>
                        <p>Накоплено баллов</p>
                    </div>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Последние действия</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="activity-content">
                            <p>Вы совершили заказ <strong>#12347</strong> на сумму 24 990 тг.</p>
                            <span class="activity-time">10 июля 2023, 14:25</span>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="activity-content">
                            <p>Вы добавили <strong>Puma RS-X</strong> в избранное</p>
                            <span class="activity-time">8 июля 2023, 09:42</span>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="activity-content">
                            <p>Заказ <strong>#12345</strong> был доставлен</p>
                            <span class="activity-time">17 мая 2023, 18:30</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="orders" class="content-section">
            <div class="section-header">
                <h1>История заказов</h1>
                <p>Просмотр и управление вашими заказами</p>
            </div>

            <div class="orders-list">
                <?php foreach ($user_orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Заказ <?php echo $order['id']; ?></h3>
                                <span class="order-date">от <?php echo $order['date']; ?></span>
                            </div>
                            <div class="order-status <?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                <?php echo $order['status']; ?>
                            </div>
                        </div>

                        <div class="order-details">
                            <div class="order-items">
                                <span><?php echo $order['items']; ?> товара</span>
                                <span>на сумму <?php echo $order['total']; ?></span>
                            </div>

                            <div class="order-actions">
                                <button class="btn-secondary">Повторить заказ</button>
                                <button class="btn-primary">Подробнее</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="favorites" class="content-section">
            <div class="section-header">
                <h1>Избранные товары</h1>
                <p>Список товаров, которые вам понравились</p>
            </div>

            <div class="favorites-grid">
                <?php foreach ($user_favorites as $item): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            <button class="favorite-btn active"><i class="fas fa-heart"></i></button>
                        </div>

                        <div class="product-info">
                            <h3><?php echo $item['name']; ?></h3>
                            <p class="product-price"><?php echo $item['price']; ?></p>

                            <div class="product-actions">
                                <button class="btn-cart"><i class="fas fa-shopping-cart"></i></button>
                                <button class="btn-primary">Купить сейчас</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="addresses" class="content-section">
            <div class="section-header">
                <h1>Адреса доставки</h1>
                <p>Управление вашими адресами доставки</p>
            </div>

            <div class="addresses-list">
                <?php foreach ($user_addresses as $address): ?>
                    <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                        <div class="address-header">
                            <h3><?php echo $address['title']; ?></h3>
                            <?php if ($address['is_default']): ?>
                                <span class="default-badge">Основной</span>
                            <?php endif; ?>
                        </div>

                        <p class="address-text"><?php echo $address['address']; ?></p>

                        <div class="address-actions">
                            <button class="btn-secondary">Редактировать</button>
                            <?php if (!$address['is_default']): ?>
                                <button class="btn-primary">Сделать основным</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="address-card add-new">
                    <div class="add-address">
                        <i class="fas fa-plus"></i>
                        <h3>Добавить новый адрес</h3>
                    </div>
                </div>
            </div>
        </section>

        <section id="settings" class="content-section">
            <div class="section-header">
                <h1>Настройки аккаунта</h1>
                <p>Управление вашими персональными данными</p>
            </div>

            <div class="settings-form">
                <form>
                    <div class="form-section">
                        <h3>Личная информация</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">Имя</label>
                                <input type="text" id="firstName" value="<?php echo explode(' ', $user['name'])[0]; ?>">
                            </div>

                            <div class="form-group">
                                <label for="lastName">Фамилия</label>
                                <input type="text" id="lastName" value="<?php echo explode(' ', $user['name'])[1] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?php echo $user['email']; ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">Телефон</label>
                            <input type="tel" id="phone" value="+7 (900) 123-45-67">
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Смена пароля</h3>

                        <div class="form-group">
                            <label for="currentPassword">Текущий пароль</label>
                            <input type="password" id="currentPassword">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="newPassword">Новый пароль</label>
                                <input type="password" id="newPassword">
                            </div>

                            <div class="form-group">
                                <label for="confirmPassword">Подтверждение пароля</label>
                                <input type="password" id="confirmPassword">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Уведомления</h3>

                        <div class="form-check">
                            <input type="checkbox" id="emailNotifications" checked>
                            <label for="emailNotifications">Email уведомления</label>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" id="smsNotifications">
                            <label for="smsNotifications">SMS уведомления</label>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" id="promoNotifications" checked>
                            <label for="promoNotifications">Уведомления о акциях и скидках</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary save-btn">Сохранить изменения</button>
                </form>
            </div>
        </section>

        <section id="loyalty" class="content-section">
            <div class="section-header">
                <h1>Программа лояльности</h1>
                <p>Ваши бонусы и привилегии</p>
            </div>

            <div class="loyalty-program">
                <div class="loyalty-status">
                    <div class="status-card">
                        <div class="status-icon gold">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="status-info">
                            <h3>Gold статус</h3>
                            <p>Действует до: 31.12.2023</p>
                        </div>
                    </div>

                    <div class="benefits-list">
                        <h3>Ваши привилегии:</h3>
                        <ul>
                            <li><i class="fas fa-check-circle"></i> Кешбэк 7% с каждой покупки</li>
                            <li><i class="fas fa-check-circle"></i> Бесплатная доставка</li>
                            <li><i class="fas fa-check-circle"></i> Приоритетная поддержка</li>
                            <li><i class="fas fa-check-circle"></i> Доступ к эксклюзивным коллекциям</li>
                            <li><i class="fas fa-check-circle"></i> Персональная скидка 15% в день рождения</li>
                        </ul>
                    </div>
                </div>

                <div class="loyalty-history">
                    <h3>История начисления баллов</h3>

                    <div class="history-table">
                        <div class="table-header">
                            <span>Дата</span>
                            <span>Операция</span>
                            <span>Баллы</span>
                        </div>

                        <div class="table-row">
                            <span>10.07.2023</span>
                            <span>Покупка заказа #12347</span>
                            <span class="points-plus">+1 050</span>
                        </div>

                        <div class="table-row">
                            <span>22.06.2023</span>
                            <span>Покупка заказа #12346</span>
                            <span class="points-plus">+350</span>
                        </div>

                        <div class="table-row">
                            <span>15.05.2023</span>
                            <span>Покупка заказа #12345</span>
                            <span class="points-plus">+595</span>
                        </div>

                        <div class="table-row">
                            <span>10.04.2023</span>
                            <span>Списание баллов</span>
                            <span class="points-minus">-750</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
    // Навигация по разделам
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            // Убираем активный класс у всех пунктов меню
            document.querySelectorAll('.nav-item').forEach(i => {
                i.classList.remove('active');
            });

            // Добавляем активный класс к текущему пункту
            this.classList.add('active');

            // Скрываем все разделы
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            // Показываем выбранный раздел
            const target = this.getAttribute('data-target');
            document.getElementById(target).classList.add('active');
        });
    });

    // Инициализация функциональности избранного
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    });
</script>
</body>
</html>