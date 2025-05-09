<?php
session_start();

$host = 'localhost';
$db = 'u68806';
$user = 'u68806';
$pass = '1921639';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM application WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['login'] = $user['login'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
</head>
<body>
    <h2>Вход</h2>
    <?php if (!empty($error)) echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>'; ?>
    <form method="post">
        <label>Логин:<br><input type="text" name="login" required></label><br><br>
        <label>Пароль:<br><input type="password" name="password" required></label><br><br>
        <button type="submit">Войти</button>
    </form>
</body>
</html>