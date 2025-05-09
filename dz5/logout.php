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

$fio = $phone = $email = $dob = $gender = $bio = '';
$languages = [];

if (!empty($_SESSION['login'])) {
    // Получение сохраненных данных
    $stmt = $pdo->prepare("SELECT fio, phone, email, dob, gender, bio, languages FROM application WHERE login = ?");
    $stmt->execute([$_SESSION['login']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $fio = $userData['fio'];
        $phone = $userData['phone'];
        $email = $userData['email'];
        $dob = $userData['dob'];
        $gender = $userData['gender'];
        $bio = $userData['bio'];
        $languages = explode(',', $userData['languages']);
    }
}

// Обработка POST-запроса (сохранение или редактирование)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fio = $_POST['fio'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $languages = $_POST['languages'] ?? [];
    $lang_str = implode(',', $languages);

    if (!empty($_SESSION['login'])) {
        // Обновление записи авторизованного пользователя
        $stmt = $pdo->prepare("UPDATE application SET fio=?, phone=?, email=?, dob=?, gender=?, bio=?, languages=? WHERE login=?");
        $stmt->execute([$fio, $phone, $email, $dob, $gender, $bio, $lang_str, $_SESSION['login']]);
        echo "<p>Данные обновлены.</p>";
        echo '<p><a href="logout.php">Выйти</a></p>';
        exit();
    } else {
        // Генерация логина и пароля
        $login = 'user' . rand(1000, 9999);
        $password = bin2hex(random_bytes(4));
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Сохранение в БД
        $stmt = $pdo->prepare("INSERT INTO application (fio, phone, email, dob, gender, bio, languages, login, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fio, $phone, $email, $dob, $gender, $bio, $lang_str, $login, $password_hash]);

        // Отображение логина/пароля
        echo "<p>Спасибо, ваши данные сохранены!</p>";
        echo "<p><strong>Логин:</strong> {$login}</p>";
        echo "<p><strong>Пароль:</strong> {$password}</p>";
        echo '<p><a href="login.php">Перейти к авторизации</a></p>';
        exit();
    }
} else {
    // Отображение формы
    if (!empty($_SESSION['login'])) {
        echo '<p>Вы вошли как <strong>' . htmlspecialchars($_SESSION['login']) . '</strong>. <a href="logout.php">Выйти</a></p>';
    }
    // Передаём значения в форму
    $data = [
        'fio' => $fio,
        'phone' => $phone,
        'email' => $email,
        'dob' => $dob,
        'gender' => $gender,
        'bio' => $bio,
        'languages' => $languages,
        'contract' => 'yes'
    ];
    $errors = [];
    include 'form.php';
}
