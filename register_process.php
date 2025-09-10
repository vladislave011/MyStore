<?php
// register_process.php

declare(strict_types=1);

global $mysqli;

session_start();
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$agree_terms = isset($_POST['agree_terms']);

$errors = [];

// === Валидация ===
if (empty($username)) {
    $errors['username'] = 'Введите имя пользователя';
} elseif (strlen($username) < 3) {
    $errors['username'] = 'Имя пользователя должно содержать минимум 3 символа';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors['username'] = 'Имя пользователя может содержать только буквы, цифры и подчёркивания';
}

if (empty($email)) {
    $errors['email'] = 'Введите email';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Некорректный формат email';
}

if (empty($password)) {
    $errors['password'] = 'Введите пароль';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Пароль должен содержать минимум 8 символов';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors['password'] = 'Пароль должен содержать хотя бы одну заглавную букву';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors['password'] = 'Пароль должен содержать хотя бы одну цифру';
} elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
    $errors['password'] = 'Пароль должен содержать хотя бы один спецсимвол';
}

if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Пароли не совпадают';
}

if (!$agree_terms) {
    $errors['agree_terms'] = 'Необходимо принять условия использования';
}

// Проверка уникальности
if (empty($errors)) {
    $check_user = $mysqli->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check_user->bind_param('ss', $email, $username);
    $check_user->execute();
    $check_user->store_result();

    if ($check_user->num_rows > 0) {
        $errors['general'] = 'Пользователь с таким email или именем уже существует';
    }
    $check_user->close();
}

// Если есть ошибки
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// === Сохраняем пользователя ===
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param('sss', $username, $email, $hashed_password);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'redirect' => 'login.php']);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при сохранении: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
