<?php
// login.php - обычный логин через форму
declare(strict_types=1);
global $mysqli;

require_once 'db.php';

// POST — обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        echo json_encode(['success' => false, 'error' => 'Введите логин и пароль']);
        exit;
    }

    // Ищем по username или email
    $sql = "SELECT id, username, email, name, password, profile_picture FROM users WHERE username = ? OR email = ? LIMIT 1";
    if (!($stmt = $mysqli->prepare($sql))) {
        echo json_encode(['success' => false, 'error' => 'Ошибка базы данных']);
        exit;
    }

    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Устанавливаем данные пользователя в сессию
            $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'picture' => $user['profile_picture']
            ];

            // Регенерируем ID сессии для безопасности
            session_regenerate_id(true);

            echo json_encode(['success' => true, 'redirect' => 'index.php']);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Неверный логин или пароль']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Пользователь не найден']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SneakerAuth - Вход</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow-x: hidden;
        }

        .sneaker-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path d="M30,30 C50,10 70,20 80,40 C90,60 70,80 50,80 C30,80 10,60 20,40 C30,20 40,30 30,30 Z" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="2"/></svg>');
            z-index: -1;
        }

        .sneaker-shape {
            position: absolute;
            background: linear-gradient(45deg, #ff6b6b, #ff9e7d);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            opacity: 0.15;
            animation: morphing 15s infinite ease-in-out;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: 15%;
            right: 10%;
            animation-delay: -5s;
            background: linear-gradient(45deg, #4ecdc4, #00b4db);
        }

        .shape-3 {
            width: 250px;
            height: 250px;
            top: 50%;
            left: 70%;
            animation-delay: -10s;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
        }

        .container {
            width: 100%;
            max-width: 450px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            transform: rotate(-5deg);
            z-index: -1;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 40px;
            color: #ff9e7d;
            margin-bottom: 10px;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(to right, #ff9e7d, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 15px rgba(255, 158, 125, 0.3);
        }

        .input-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #ff9e7d;
            font-size: 18px;
        }

        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(to right, #ff6b6b, #ff9e7d);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
        }

        .google-btn-container {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
        }

        .register-link {
            text-align: center;
            font-size: 15px;
        }

        .register-link p {
            margin-bottom: 10px;
        }

        .register-link a {
            color: #ff9e7d;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #ff6b6b;
            text-decoration: underline;
        }

        .sneaker-animation {
            position: absolute;
            bottom: -50px;
            right: -50px;
            opacity: 0.1;
            font-size: 200px;
            transform: rotate(-15deg);
            color: #ff9e7d;
        }

        .message {
            display: none;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        @keyframes morphing {
            0% {
                border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            }
            25% {
                border-radius: 58% 42% 75% 25% / 76% 46% 54% 24%;
            }
            50% {
                border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%;
            }
            75% {
                border-radius: 33% 67% 58% 42% / 63% 68% 32% 37%;
            }
            100% {
                border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            }
        }

        @media (max-width: 500px) {
            .container {
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 24px;
            }

            .input-group input {
                padding: 12px 15px 12px 45px;
            }

            .sneaker-animation {
                font-size: 150px;
                bottom: -40px;
                right: -40px;
            }
        }
    </style>
</head>
<body>
<div class="sneaker-background"></div>
<div class="sneaker-shape shape-1"></div>
<div class="sneaker-shape shape-2"></div>
<div class="sneaker-shape shape-3"></div>

<div class="container">
    <div class="logo">
        <i class="fas fa-shoe-prints"></i>
        <h1>Lynor Shop</h1>
    </div>

    <div id="message" class="message"></div>

    <form id="login-form" action="login.php" method="POST">
        <div class="input-group">
            <i class="fas fa-user"></i>
            <label>
                <input type="text" name="username" placeholder="Имя пользователя или Email" required>
            </label>
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <label>
                <input type="password" name="password" placeholder="Пароль" required>
            </label>
        </div>

        <button type="submit" class="btn">Войти</button>
    </form>

    <div class="google-btn-container">
        <div id="google-signin-button"></div>
    </div>

    <div class="register-link">
        <p>Ещё нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>

    <div class="sneaker-animation">
        <i class="fas fa-sneaker"></i>
    </div>
</div>

<!-- Google API - загружаем один раз с использованием onload -->
<script src="https://accounts.google.com/gsi/client" onload="initGoogle()" async defer></script>

<script>
    // Глобальная функция для обработки входа через Google
    function handleGoogleSignIn(response) {
        fetch('google-auth.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ credential: response.credential })
        })
            .then(res => res.json())
            .then(data => {
                const messageDiv = document.getElementById('message');
                if (data.success) {
                    messageDiv.textContent = "Вход через Google успешен!";
                    messageDiv.className = 'message success';
                    messageDiv.style.display = 'block';
                    setTimeout(() => location.href = data.redirect, 1000);
                } else {
                    messageDiv.textContent = data.error;
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                }
            })
            .catch(() => {
                const messageDiv = document.getElementById('message');
                messageDiv.textContent = "Ошибка при входе через Google";
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            });
    }

    // Инициализация Google API
    function initGoogle() {
        if (typeof google !== 'undefined') {
            google.accounts.id.initialize({
                client_id: "768054744865-cj4qpmvqd07vm6bc53ef7psob1l9eii9.apps.googleusercontent.com",
                callback: handleGoogleSignIn
            });
            google.accounts.id.renderButton(
                document.getElementById("google-signin-button"),
                { theme: "filled_black", size: "large", shape: "pill", width: 400 }
            );
        } else {
            console.error('Google API не загрузилась');
        }
    }

    // Обработка обычной формы входа
    document.addEventListener('DOMContentLoaded', function () {
        const loginForm = document.getElementById('login-form');
        const messageDiv = document.getElementById('message');

        function showMessage(text, type) {
            messageDiv.textContent = text;
            messageDiv.className = `message ${type}`;
            messageDiv.style.display = 'block';
            setTimeout(() => { messageDiv.style.display = 'none'; }, 4000);
        }

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('login.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showMessage("Успешный вход!", "success");
                        setTimeout(() => location.href = data.redirect, 1000);
                    } else {
                        showMessage(data.error, "error");
                    }
                })
                .catch(() => showMessage("Ошибка запроса", "error"));
        });

        // Эффект параллакса для фона
        document.addEventListener('mousemove', function(e) {
            const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
            const moveY = (e.clientY - window.innerHeight / 2) * 0.01;

            document.querySelector('.shape-1').style.transform = `translate(${moveX}px, ${moveY}px)`;
            document.querySelector('.shape-2').style.transform = `translate(${-moveX}px, ${-moveY}px)`;
            document.querySelector('.shape-3').style.transform = `translate(${-moveX * 1.5}px, ${moveY * 1.5}px)`;
        });
    });
</script>
</body>
</html>