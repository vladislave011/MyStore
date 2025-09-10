<?php
session_start();
header('Content-Type: application/json');

// Подключение к базе данных
$mysqli = new mysqli('localhost', 'root', 'Vlad7418529630@', 'my_store');
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Ошибка подключения к БД']);
    exit();
}
$mysqli->set_charset("utf8");

// Проверяем сессию
if (!isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = session_id();
}
$cart_session = $_SESSION['cart_session'];

// Получаем данные из POST запроса
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
// --- Исправлено: размер должен обрабатываться здесь, сразу после получения ID ---
$size = isset($_POST['size']) ? intval($_POST['size']) : 36; // Размер по умолчанию

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
    exit();
}

// Проверяем существование товара
$check_product = $mysqli->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
$check_product->bind_param("i", $product_id);
$check_product->execute();
$check_product->store_result();
if ($check_product->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден']);
    exit();
}
$check_product->bind_result($prod_id, $prod_name, $prod_price, $prod_image);
$check_product->fetch();
$check_product->close();

// Добавляем или обновляем товар в корзине
// --- Важно: Условие WHERE теперь включает size ---
$stmt = $mysqli->prepare("SELECT id, quantity FROM cart WHERE session_id=? AND product_id=? AND size=?");
$stmt->bind_param("sii", $cart_session, $product_id, $size);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->bind_result($cart_id, $quantity);
    $stmt->fetch();
    $new_quantity = $quantity + 1;
    $update = $mysqli->prepare("UPDATE cart SET quantity=? WHERE id=?");
    $update->bind_param("ii", $new_quantity, $cart_id);
    $update->execute();
    $update->close();
    $action = 'updated';
} else {
    // --- Важно: При вставке также сохраняется размер ---
    $insert = $mysqli->prepare("INSERT INTO cart (product_id, quantity, session_id, size) VALUES (?, 1, ?, ?)");
    $insert->bind_param("isi", $product_id, $cart_session, $size);
    $insert->execute();
    $insert->close();
    $action = 'added';
}
$stmt->close();

// Получаем общее количество товаров в корзине
$stmt_count = $mysqli->prepare("SELECT SUM(quantity) FROM cart WHERE session_id=?");
$stmt_count->bind_param("s", $cart_session);
$stmt_count->execute();
$stmt_count->bind_result($total_count);
$stmt_count->fetch();
$stmt_count->close();

echo json_encode([
    'success' => true,
    'action' => $action,
    'product_name' => $prod_name,
    'product_price' => $prod_price,
    'cart_count' => $total_count ?: 0,
    'message' => 'Товар добавлен в корзину!'
]);

$mysqli->close();
?>