<?php

$page_title = "Регистрация — Корочки.нет";
require_once 'includes/header.php'; // Подключаем heder

$errors = []; // Массив для ошибок
$success = false;

// Проверка, отправки форм (метод POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем и очищаем данные из формы
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullName = trim($_POST['fullName']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    // TODO: Здесь должна быть серверная валидация данных.


    // Провка, не занят ли логин/email
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE login = ? OR email = ?");
    $stmt->bind_param("ss", $login, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Пользователь с таким логином или email уже существует.";
    }
    $stmt->close();

    // Если нет ошибок, регистрируем пользователя
    if (empty($errors)) {
        // Хэш паролей
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("INSERT INTO users (login, password_hash, full_name, phone, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $login, $password_hash, $fullName, $phone, $email);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Ошибка при регистрации. Пожалуйста, попробуйте снова.";
        }
        $stmt->close();
    }
}
?>

<div class="form-container">
    <h1>Регистрация</h1>

    <?php if ($success): ?>
        <div style="color: green; text-align: center; margin-bottom: 20px;">
            Регистрация прошла успешно! Теперь вы можете <a href="login.php">войти</a>.
        </div>
    <?php else: ?>
        
        <?php if (!empty($errors)): ?>
            <div style="color: #e74c3c; text-align: center; margin-bottom: 20px;">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form id="registerForm" action="register.php" method="POST">
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="fullName">ФИО</label>
                <input type="text" id="fullName" name="fullName" required>
            </div>
            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" placeholder="8(XXX)XXX-XX-XX" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="submit-btn">Создать аккаунт</button>
            <div class="switch-form-link">
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
            </div>
        </form>

    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php'; // Подключаем footer
?>
