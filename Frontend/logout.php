<?php

session_start(); // Подключение сессии
session_destroy(); // Разрыв сессии

header("Location: login.php"); // Переход на стр. входа
exit();
?>
