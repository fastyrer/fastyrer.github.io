<?php
// login.php
session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if (!empty($login) && !empty($pass)) {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM application WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['login'] = $login;
            $_SESSION['uid'] = $user['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Неверный логин или пароль.";
        }
    } else {
        $error = "Пожалуйста, заполните оба поля.";
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
<h2>Авторизация</h2>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" action="">
    <label>Логин:<br>
        <input type="text" name="login">
    </label><br><br>
    <label>Пароль:<br>
        <input type="password" name="pass">
    </label><br><br>
    <input type="submit" value="Войти">
</form>
</body>
</html>
