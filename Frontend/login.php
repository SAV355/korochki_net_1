<?php

$page_title = "Авторизация — Корочки.нет";
require_once 'includes/header.php'; // Подключаем heder, он запустит session_start() из config.php

$error = null;

// Проверка авторизован ли пользователь, перенаправляем его в ЛК
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT id, password_hash, role, full_name FROM users WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Проверяем пароль
        if (password_verify($password, $user['password_hash'])) {
            // Пароль верный, сохраняем данные в сессию
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            // Перенаправляем в зависимости от роли
            if ($user['role'] === 'admin') {
                header("Location: admin_panel.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        }
    }
    // Если что-то пошло не так
    $error = "Неверный логин или пароль!";
    $stmt->close();
}
?>

<div class="form-container">
    <h1>Вход в систему</h1>

    <?php if ($error): ?>
        <div class="error-message" style="display:block; text-align:center; background-color: #fdd; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="login">Логин</label>
            <input type="text" id="login" name="login" required>
        </div>
        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="submit-btn">Войти</button>
        <div class="switch-form-link">
            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
        </div>
    </form>
</div>

<?php
require_once 'includes/footer.php';
?>
