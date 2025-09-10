<?php
// google-auth.php
global $mysqli;
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['credential'] ?? null;

if (!$data || !isset($data['credential'])) {
    error_log("Google auth error: No credential received. Input: " . print_r($data, true));
    echo json_encode(["success" => false, "error" => "Нет токена Google"]);
    exit;
}

// Декодируем JWT токен
function decodeJwt($jwt) {
    $tks = explode('.', $jwt);
    if (count($tks) != 3) return null;

    $payload = $tks[1];
    $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload));
    return json_decode($payload, true);
}

$payload = decodeJwt($data['credential']);
if (!$payload) {
    error_log("Google auth error: Failed to decode JWT");
    echo json_encode(["success" => false, "error" => "Ошибка декодирования токена"]);
    exit;
}

$google_id = isset($payload['sub']) ? $payload['sub'] : null;
$name = isset($payload['name']) ? $payload['name'] : '';
$email = isset($payload['email']) ? $payload['email'] : '';
$picture = isset($payload['picture']) ? $payload['picture'] : '';

if (!$google_id || !$email) {
    error_log("Google auth error: Missing required fields. Payload: " . print_r($payload, true));
    echo json_encode(["success" => false, "error" => "Недостаточно данных от Google"]);
    exit;
}

// Сохраняем или обновляем пользователя
$stmt = $mysqli->prepare("SELECT id FROM users WHERE google_id = ? OR email = ?");
$stmt->bind_param("ss", $google_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];

    $stmt = $mysqli->prepare("UPDATE users SET name=?, profile_picture=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssi", $name, $picture, $user_id);
    $stmt->execute();
} else {
    $stmt = $mysqli->prepare("INSERT INTO users (google_id, name, email, profile_picture) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $google_id, $name, $email, $picture);

    if (!$stmt->execute()) {
        error_log("Failed to insert user: " . $stmt->error);
        echo json_encode(["success" => false, "error" => "Ошибка сохранения пользователя"]);
        exit;
    }

    $user_id = $stmt->insert_id;
}

// Сохраняем сессию
$_SESSION['user'] = [
    'id' => $user_id,
    'name' => $name,
    'email' => $email,
    'picture' => $picture
];

// Закрываем соединения
$stmt->close();

// Убедимся, что сессия записана
session_write_close();

error_log("User authenticated successfully: " . $email);

echo json_encode([
    "success" => true,
    "redirect" => "index.php?auth=success",
    "user" => [
        "id" => $user_id,
        "name" => $name,
        "email" => $email,
        "picture" => $picture
    ]
]);