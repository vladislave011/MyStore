<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $mysqli;

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        die('Заполните все поля!');
    }

    // Забираем username и profile_picture (у тебя именно такие поля в БД)
    $stmt = $mysqli->prepare("SELECT id, username, email, password, name, profile_picture FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // сохраняем всё в сессию
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],         // ← вот оно
            'email' => $user['email'],
            'picture' => $user['profile_picture']   // ← исправили на profile_picture
        ];

        header("Location: index.php");
        exit;
    } else {
        die('Неверный email или пароль!');
    }
}
