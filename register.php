<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SneakerAuth - Регистрация</title>
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

        .error-message {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
            min-height: 18px;
        }

        .password-strength {
            margin-top: 5px;
            height: 5px;
            border-radius: 3px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 3px;
            transition: width 0.3s ease, background 0.3s ease;
        }

        .password-requirements {
            margin-top: 10px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }

        .requirement {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .requirement i {
            margin-right: 5px;
            font-size: 10px;
        }

        .terms {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .terms input {
            margin-right: 8px;
            accent-color: #ff9e7d;
        }

        .terms a {
            color: #ff9e7d;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .terms a:hover {
            color: #ff6b6b;
            text-decoration: underline;
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

        .google-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }

        .google-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
        }

        .google-btn i {
            margin-right: 10px;
            font-size: 18px;
            color: #ff6b6b;
        }

        .login-link {
            text-align: center;
            font-size: 15px;
        }

        .login-link p {
            margin-bottom: 10px;
        }

        .login-link a {
            color: #ff9e7d;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
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

    <form id="registerForm">
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" id="username" placeholder="Имя пользователя" required>
            <div class="error-message" id="username-error"></div>
        </div>

        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" id="email" placeholder="Электронная почта" required>
            <div class="error-message" id="email-error"></div>
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" id="password" placeholder="Пароль" required>
            <div class="password-strength">
                <div class="password-strength-bar" id="passwordStrengthBar"></div>
            </div>
            <div class="password-requirements">
                <div class="requirement" id="lengthReq"><i class="fas fa-circle"></i> Не менее 8 символов</div>
                <div class="requirement" id="uppercaseReq"><i class="fas fa-circle"></i> Заглавные буквы</div>
                <div class="requirement" id="numberReq"><i class="fas fa-circle"></i> Цифры</div>
                <div class="requirement" id="specialReq"><i class="fas fa-circle"></i> Специальные символы</div>
            </div>
            <div class="error-message" id="password-error"></div>
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Подтверждение пароля" required>
            <div class="error-message" id="confirm_password-error"></div>
        </div>

        <div class="terms">
            <input type="checkbox" name="agree_terms" id="agree_terms" required>
            <label for="agree_terms">Я согласен с <a href="#">условиями использования</a> и <a href="#">политикой конфиденциальности</a></label>
            <div class="error-message" id="agree_terms-error"></div>
        </div>

        <button type="submit" class="btn">Зарегистрироваться</button>
    </form>

    <div class="google-login">
        <button class="google-btn">
            <i class="fab fa-google"></i>
            Зарегистрироваться через Google
        </button>
    </div>

    <div class="login-link">
        <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </div>

    <div class="sneaker-animation">
        <i class="fas fa-sneaker"></i>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('passwordStrengthBar');

        const requirements = {
            length: document.getElementById('lengthReq'),
            uppercase: document.getElementById('uppercaseReq'),
            number: document.getElementById('numberReq'),
            special: document.getElementById('specialReq')
        };

        // Очистка ошибок
        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
            });
        }

        // Отображение ошибок
        function showErrors(errors) {
            clearErrors();
            for (const field in errors) {
                const errorElement = document.getElementById(`${field}-error`);
                if (errorElement) {
                    errorElement.textContent = errors[field];
                }
            }
        }

        // Проверка сложности пароля
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            // Проверка длины
            if (password.length >= 8) {
                strength += 25;
                requirements.length.innerHTML = '<i class="fas fa-check" style="color: #4ecdc4;"></i> Не менее 8 символов';
            } else {
                requirements.length.innerHTML = '<i class="fas fa-circle"></i> Не менее 8 символов';
            }

            // Проверка на заглавные буквы
            if (/[A-Z]/.test(password)) {
                strength += 25;
                requirements.uppercase.innerHTML = '<i class="fas fa-check" style="color: #4ecdc4;"></i> Заглавные буквы';
            } else {
                requirements.uppercase.innerHTML = '<i class="fas fa-circle"></i> Заглавные буквы';
            }

            // Проверка на цифры
            if (/[0-9]/.test(password)) {
                strength += 25;
                requirements.number.innerHTML = '<i class="fas fa-check" style="color: #4ecdc4;"></i> Цифры';
            } else {
                requirements.number.innerHTML = '<i class="fas fa-circle"></i> Цифры';
            }

            // Проверка на специальные символы
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 25;
                requirements.special.innerHTML = '<i class="fas fa-check" style="color: #4ecdc4;"></i> Специальные символы';
            } else {
                requirements.special.innerHTML = '<i class="fas fa-circle"></i> Специальные символы';
            }

            // Обновление индикатора сложности пароля
            strengthBar.style.width = strength + '%';

            if (strength < 50) {
                strengthBar.style.background = '#ff6b6b';
            } else if (strength < 100) {
                strengthBar.style.background = '#ff9e7d';
            } else {
                strengthBar.style.background = '#4ecdc4';
            }
        });

        // Проверка совпадения паролей
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.style.boxShadow = '0 0 15px rgba(255, 107, 107, 0.3)';
            } else {
                this.style.boxShadow = '0 0 15px rgba(78, 205, 196, 0.3)';
            }
        });

        // Обработка отправки формы
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Базовая валидация на клиенте
            if (passwordInput.value !== confirmPasswordInput.value) {
                alert('Пароли не совпадают!');
                return;
            }

            if (!document.getElementById('agree_terms').checked) {
                alert('Необходимо принять условия использования');
                return;
            }

            const formData = new FormData(form);

            try {
                const response = await fetch('register_process.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = result.redirect;
                } else {
                    if (result.errors) {
                        showErrors(result.errors);
                    } else if (result.error) {
                        alert(result.error);
                    }
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при отправке формы');
            }
        });

        // Анимация для кнопок
        const buttons = document.querySelectorAll('button');

        buttons.forEach(button => {
            button.addEventListener('mousedown', function() {
                this.style.transform = 'translateY(0)';
            });

            button.addEventListener('mouseup', function() {
                this.style.transform = 'translateY(-3px)';
            });
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