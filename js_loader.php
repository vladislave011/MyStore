<?php
// Можно проверять права пользователя
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403); // Доступ запрещен
    exit('Access denied');
}

// Заголовок, чтобы браузер понял, что это JS
header('Content-Type: application/javascript');

// Читаем JS файл и выводим его
$js_file = __DIR__ . '/private_js/index.js';
if (file_exists($js_file)) {
    readfile($js_file);
} else {
    http_response_code(404);
    echo "console.error('File not found');";
}
?>
