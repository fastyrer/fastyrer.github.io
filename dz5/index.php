<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$host = 'localhost';
$db   = 'u68806';
$user = 'u68806';
$pass = '1921639';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Обработка POST-запроса (сохранение или редактирование)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fio = $_POST['fio'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $bio = $_POST['bio'] ?? '';

    if (!empty($_SESSION['login'])) {
        // Обновление записи авторизованного пользователя
        $stmt = $pdo->prepare("UPDATE application SET fio=?, phone=?, email=?, dob=?, gender=?, bio=? WHERE login=?");
        $stmt->execute([$fio, $phone, $email, $dob, $gender, $bio, $_SESSION['login']]);
        echo "<p>Данные обновлены.</p>";
        exit();
    } else {
        // Генерация логина и пароля
        $login = 'user' . rand(1000, 9999);
        $password = bin2hex(random_bytes(4));
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Сохранение в БД
        $stmt = $pdo->prepare("INSERT INTO application (fio, phone, email, dob, gender, bio, login, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fio, $phone, $email, $dob, $gender, $bio, $login, $password_hash]);

        // Отображение логина/пароля
        echo "<p>Спасибо, ваши данные сохранены!</p>";
        echo "<p><strong>Логин:</strong> {$login}</p>";
        echo "<p><strong>Пароль:</strong> {$password}</p>";
        echo '<p><a href="login.php">Перейти к авторизации</a></p>';
        exit();
    }
} else {
    // Отображение формы
    include 'form.php';
}
