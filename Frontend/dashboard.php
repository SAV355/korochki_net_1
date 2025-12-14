<?php

$page_title = "Мои заявки — Корочки.нет";
require_once 'includes/header.php'; // Подключает config. и session_start()

// ---- ЗАЩИТА СТРАНИЦЫ ----
// Проверка сессии, нет ID пользователя, значит, он не авторизован
if (!isset($_SESSION['user_id'])) {
    // Переход на страницу входа
    header("Location: login.php");
    exit();
}
// Проверка, если user-админ, перенаправляем в админ-панель
if ($_SESSION['role'] === 'admin') {
    header("Location: admin_panel.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Получаем заявки пользователя из БД
$query = "SELECT a.start_date, a.status, c.name as course_name 
        FROM applications a
        JOIN courses c ON a.course_id = c.id
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="content-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Мои заявки (<?= htmlspecialchars($_SESSION['full_name']) ?>)</h2>
        <a href="logout.php">Выйти</a>
    </div>

    <a href="create_application.php" class="submit-btn" style="display:inline-block; width:auto; text-decoration: none; margin-bottom: 20px;">Создать новую заявку</a>
    
    <?php if (empty($applications)): ?>
        <p>У вас пока нет заявок.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Курс</th>
                    <th>Дата начала</th>
                    <th>Статус</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['course_name']) ?></td>
                        <td><?= date("d.m.Y", strtotime($app['start_date'])) ?></td>
                        <td><?= htmlspecialchars($app['status']) ?></td>
                        <td>
                            <?php if ($app['status'] === 'Обучение завершено'): ?>
                                <button>Оставить отзыв</button> <!-- Тут может быть логика отзыва -->
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<?php
require_once 'includes/footer.php';
?>
