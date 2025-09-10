<?php

require 'db.php';

if (!isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = session_id();
}
$cart_session = $_SESSION['cart_session'];

// Получение всех товаров
function getProducts() {
    global $mysqli;
    $res = $mysqli->query("SELECT * FROM products");
    $products = [];
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

// Получение уникальных категорий
function getCategories() {
    global $mysqli;
    $res = $mysqli->query("SELECT DISTINCT category FROM products");
    $categories = ['Все'];
    while ($row = $res->fetch_assoc()) {
        if (!empty($row['category'])) $categories[] = $row['category'];
    }
    return $categories;
}

// Получение количества товаров в корзине
function getCartCount() {
    global $mysqli, $cart_session;
    $stmt = $mysqli->prepare("SELECT SUM(quantity) FROM cart WHERE session_id=?");
    $stmt->bind_param("s", $cart_session);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return isset($count) ? $count : 0;
}

// Получение товаров корзины
function getCartItems() {
    global $mysqli, $cart_session;
    $stmt = $mysqli->prepare("
        SELECT c.id, p.name, p.price, c.quantity
        FROM cart c
        JOIN products p ON c.product_id=p.id
        WHERE c.session_id=?
    ");
    $stmt->bind_param("s", $cart_session);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = [];
    while ($row = $res->fetch_assoc()) $items[] = $row;
    $stmt->close();
    return $items;
}
?>
