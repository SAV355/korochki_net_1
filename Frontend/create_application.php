<?php
// Файл: create_application.php
$page_title = "Новая заявка — Корочки.нет";
require_once 'includes/header.php';

// ---- ЗАЩИТА СТРАНИЦЫ ----
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = null;

// Обработка формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $start_date_raw = $_POST['start_date']; // Формат ДД.ММ.ГГГГ
    $payment_method = $_POST['payment_method'];

    // Преобразуем дату в формат MySQL (ГГГГ-ММ-ДД)
    $start_date = DateTime::createFromFormat('d.m.Y', $start_date_raw)->format('Y-m-d');

    $stmt = $mysqli->prepare("INSERT INTO applications (user_id, course_id, start_date, payment_method) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $course_id, $start_date, $payment_method);
    
    if ($stmt->execute()) {
        $success_message = "Ваша заявка успешно создана! Вы будете перенаправлены в личный кабинет.";
        // Перенаправляем через 3 секунды
        header("refresh:3;url=dashboard.php");
    } else {
        // Обработка ошибки
    }
    $stmt->close();
}

// Получаем список курсов для выпадающего списка
$courses_result = $mysqli->query("SELECT id, name FROM courses ORDER BY name");
?>

<div class="form-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Создание заявки</h2>
        <a href="dashboard.php">Назад в кабинет</a>
    </div>

    <?php if ($success_message): ?>
        <div style="color: green; text-align: center; margin: 20px 0;"><?= $success_message ?></div>
    <?php else: ?>
        <form action="create_application.php" method="POST">
            <div class="form-group">
                <label for="course">Наименование курса</label>
                <select id="course" name="course_id" required>
                    <option value="" disabled selected>-- Выберите курс --</option>
                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                        <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="startDate">Дата начала обучения</label>
                <input type="text" id="startDate" name="start_date" placeholder="ДД.ММ.ГГГГ" required>
            </div>

            <div class="form-group">
                <label>Способ оплаты</label>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <label><input type="radio" name="payment_method" value="cash" checked> Наличными</label>
                    <label><input type="radio" name="payment_method" value="phone"> По номеру телефона</label>
                </div>
            </div>

            <button type="submit" class="submit-btn">Отправить заявку</button>
        </form>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
