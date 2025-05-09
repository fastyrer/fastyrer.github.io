<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
echo "<h2>Файл index.php загружен успешно.</h2>";

// Выводим имя пользователя, если авторизован
if (!empty($_SESSION['login'])) {
    echo "<p>Вы вошли как <strong>" . htmlspecialchars($_SESSION['login']) . "</strong></p>";
} else {
    echo "<p>Вы не авторизованы.</p>";
}

// Проверим подключение к БД (если нужно)
/*
$host = 'localhost';
$db   = 'u68806';
$user = 'u68806';
$pass = '1921639';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "<p>Подключение к БД: УСПЕШНО</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>Ошибка подключения к БД: " . $e->getMessage() . "</p>";
}
*/
?>
