<?php

$page_title = "Панель Администратора — Корочки.нет";
require_once 'includes/header.php';

// ---- ДВОЙНАЯ ЗАЩИТА СТР. ----
// 1. Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// 2. Проверка, является ли пользователь администратором
if ($_SESSION['role'] !== 'admin') {
    // Если не админ, отправляем в его личный кабинет
    header("Location: dashboard.php");
    exit();
}

$update_message = null;
// Обработка формы обновления 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['statuses'])) {
    $statuses = $_POST['statuses'];
    $stmt = $mysqli->prepare("UPDATE applications SET status = ? WHERE id = ?");
    
    foreach ($statuses as $app_id => $new_status) {
        $stmt->bind_param("si", $new_status, $app_id);
        $stmt->execute();
    }
    $stmt->close();
    $update_message = "Статусы успешно обновлены!";
}

// Получаем все заявки из БД, объединяя данные из 3-х таблиц
$query = "SELECT a.id, a.start_date, a.status, u.full_name as user_name, c.name as course_name 
        FROM applications a
        JOIN users u ON a.user_id = u.id
        JOIN courses c ON a.course_id = c.id
        ORDER BY a.created_at DESC";
$applications = $mysqli->query($query)->fetch_all(MYSQLI_ASSOC);
$statuses_list = ['Новая', 'Идет обучение', 'Обучение завершено'];
?>
<div class="content-container" style="max-width: 1100px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Панель Администратора (<?= htmlspecialchars($_SESSION['full_name']) ?>)</h2>
        <a href="logout.php">Выйти</a>
    </div>

    <?php if ($update_message): ?>
        <div style="color: green; text-align: center; margin: 20px 0;"><?= $update_message ?></div>
    <?php endif; ?>

    <?php if (empty($applications)): ?>
        <p>На данный момент заявок нет.</p>
    <?php else: ?>
        <form action="admin_panel.php" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь (ФИО)</th>
                        <th>Курс</th>
                        <th>Дата начала</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= $app['id'] ?></td>
                        <td><?= htmlspecialchars($app['user_name']) ?></td>
                        <td><?= htmlspecialchars($app['course_name']) ?></td>
                        <td><?= date("d.m.Y", strtotime($app['start_date'])) ?></td>
                        <td>
                            <select name="statuses[<?= $app['id'] ?>]">
                                <?php foreach ($statuses_list as $status): ?>
                                    <option value="<?= $status ?>" <?= ($app['status'] == $status) ? 'selected' : '' ?>>
                                        <?= $status ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="submit-btn" style="margin-top: 20px;">Сохранить изменения</button>
        </form>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
