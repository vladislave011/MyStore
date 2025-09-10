<?php
// db.php - исправленная версия
// Включаем показ ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = '127.0.0.1';
$port = 3306;
$db   = 'my_store';
$user = 'root';
$pass = 'Vlad7418529630@';

$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) {
    die('Ошибка подключения (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// ===== Класс для хранения сессий в БД (PHP 8.0+) =====
class MySQLSessionHandler implements SessionHandlerInterface {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(255) PRIMARY KEY,
            data MEDIUMTEXT NOT NULL,
            timestamp INT(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->mysqli->query($sql);
    }

    public function open(string $path, string $name): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string {
        $stmt = $this->mysqli->prepare("SELECT data FROM sessions WHERE id = ? LIMIT 1");
        if (!$stmt) {
            return '';
        }
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            $stmt->close();
            return '';
        }
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return $row['data'] ?? '';
    }

    public function write(string $id, string $data): bool {
        $time = time();
        $stmt = $this->mysqli->prepare("REPLACE INTO sessions (id, data, timestamp) VALUES (?, ?, ?)");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ssi', $id, $data, $time);
        $res = $stmt->execute();
        $stmt->close();
        return (bool)$res;
    }

    public function destroy(string $id): bool {
        $stmt = $this->mysqli->prepare("DELETE FROM sessions WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $id);
        $res = $stmt->execute();
        $stmt->close();
        return (bool)$res;
    }

    public function gc(int $max_lifetime): int {
        $old = time() - $max_lifetime;
        $stmt = $this->mysqli->prepare("DELETE FROM sessions WHERE timestamp < ?");
        if (!$stmt) {
            return 0;
        }
        $stmt->bind_param('i', $old);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }
}

// Функция для безопасного выполнения запросов
function dbQuery($sql, $params = []): array
{
    global $mysqli;

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => $mysqli->error];
    }

    if (!empty($params)) {
        $types = '';
        foreach ($params as $p) {
            if (is_int($p)) $types .= 'i';
            elseif (is_float($p)) $types .= 'd';
            else $types .= 's';
        }

        $refs = [];
        $refs[] = &$types;
        foreach ($params as $param) {
            $refs[] = &$param;
        }
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        return ['success' => false, 'error' => $err];
    }

    $result = $stmt->get_result();
    if ($result !== false) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return ['success' => true, 'data' => $data];
    } else {
        $insertId = $stmt->insert_id;
        $stmt->close();
        return ['success' => true, 'insert_id' => $insertId];
    }
}

// Создаём таблицу users если нужно
$checkTable = dbQuery("SHOW TABLES LIKE 'users'");
if (!$checkTable['success'] || count($checkTable['data'] ?? []) === 0) {
    $createTable = dbQuery("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            google_id VARCHAR(255) UNIQUE,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            profile_picture VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    if (!$createTable['success']) {
        die('Ошибка создания таблицы users: ' . $createTable['error']);
    }
}

// Настройки сессии
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

// Всегда закрываем существующую сессию перед настройкой обработчика
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$params = [
    'lifetime' => 86400, // 1 день вместо 0
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax' // Добавьте это
];

$host = $_SERVER['HTTP_HOST'] ?? '';
if ($host !== '' && !in_array($host, ['localhost', '127.0.0.1'])) {
    if (str_contains($host, ':')) $host = explode(':', $host)[0];
    $params['domain'] = $host;
}

session_set_cookie_params($params);

$sessionHandler = new MySQLSessionHandler($mysqli);
session_set_save_handler($sessionHandler, true);
register_shutdown_function('session_write_close');

session_start();