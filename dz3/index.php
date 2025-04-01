<?php
session_start();

// Функция для проверки полей формы
function validate_field($field, $pattern, $error_message) {
    if (!preg_match($pattern, $field)) {
        return $error_message;
    }
    return null;
}

$errors = [];
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $data['fio'] = trim($_POST['fio']);
    $data['phone'] = trim($_POST['phone']);
    $data['email'] = trim($_POST['email']);
    $data['dob'] = trim($_POST['dob']);
    $data['gender'] = $_POST['gender'] ?? '';
    $data['languages'] = $_POST['languages'] ?? [];
    $data['bio'] = trim($_POST['bio']);
    $data['contract'] = isset($_POST['contract']) ? 'yes' : '';

    // Валидация
    $errors['fio'] = validate_field($data['fio'], "/^[\p{L} \-]+$/u", "ФИО может содержать только буквы, пробел и дефис.");
    $errors['phone'] = validate_field($data['phone'], "/^\+?[0-9\-\s]+$/", "Телефон должен содержать только цифры, пробелы, дефис и знак '+'.");
    $errors['email'] = validate_field($data['email'], "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", "Некорректный формат e-mail.");
    $errors['dob'] = empty($data['dob']) ? "Дата рождения обязательна." : null;
    $errors['gender'] = empty($data['gender']) ? "Выберите пол." : null;
    $errors['languages'] = empty($data['languages']) ? "Выберите хотя бы один язык." : null;
    $errors['bio'] = validate_field($data['bio'], "/^[\p{L}0-9.,!\-\s]+$/u", "Биография содержит недопустимые символы.");
    $errors['contract'] = empty($data['contract']) ? "Необходимо согласие с контрактом." : null;

    // Удаляем пустые ошибки
    $errors = array_filter($errors);

    if (!empty($errors)) {
        // Сохраняем ошибки в Cookies на сессию
        setcookie('errors', json_encode($errors), 0, '/');
        setcookie('form_data', json_encode($data), 0, '/');
        header("Location: index.php");
        exit();
    } else {
        // Сохраняем корректные данные в Cookies на 1 год
        foreach ($data as $key => $value) {
            setcookie($key, is_array($value) ? json_encode($value) : $value, time() + 31536000, '/');
        }
        // Удаляем ошибки
        setcookie('errors', '', time() - 3600, '/');
        setcookie('form_data', '', time() - 3600, '/');
        header("Location: index.php");
        exit();
    }
}

// Получаем сохраненные ошибки и данные
$errors = isset($_COOKIE['errors']) ? json_decode($_COOKIE['errors'], true) : [];
$data = isset($_COOKIE['form_data']) ? json_decode($_COOKIE['form_data'], true) : [];

// Удаляем использованные Cookies
setcookie('errors', '', time() - 3600, '/');
setcookie('form_data', '', time() - 3600, '/');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форма регистрации</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error { color: red; font-size: 0.9em; }
        .error-field { border: 1px solid red; }
    </style>
</head>
<body>
    <form action="index.php" method="post">
        <h2>Регистрация</h2>
        <label for="fio">ФИО:</label>
        <input type="text" id="fio" name="fio" value="<?= htmlspecialchars($data['fio'] ?? '') ?>" class="<?= isset($errors['fio']) ? 'error-field' : '' ?>">
        <div class="error"><?= $errors['fio'] ?? '' ?></div>

        <label for="phone">Телефон:</label>
        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" class="<?= isset($errors['phone']) ? 'error-field' : '' ?>">
        <div class="error"><?= $errors['phone'] ?? '' ?></div>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
        <div class="error"><?= $errors['email'] ?? '' ?></div>

        <label for="dob">Дата рождения:</label>
        <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($data['dob'] ?? '') ?>" class="<?= isset($errors['dob']) ? 'error-field' : '' ?>">
        <div class="error"><?= $errors['dob'] ?? '' ?></div>

        <label>Пол:</label>
        <label><input type="radio" name="gender" value="male" <?= isset($data['gender']) && $data['gender'] === 'male' ? 'checked' : '' ?>> Мужской</label>
        <label><input type="radio" name="gender" value="female" <?= isset($data['gender']) && $data['gender'] === 'female' ? 'checked' : '' ?>> Женский</label>
        <div class="error"><?= $errors['gender'] ?? '' ?></div>

        <label for="bio">Биография:</label>
        <textarea id="bio" name="bio" rows="4" class="<?= isset($errors['bio']) ? 'error-field' : '' ?>"><?= htmlspecialchars($data['bio'] ?? '') ?></textarea>
        <div class="error"><?= $errors['bio'] ?? '' ?></div>

        <label><input type="checkbox" name="contract" <?= isset($data['contract']) && $data['contract'] === 'yes' ? 'checked' : '' ?>> С контрактом ознакомлен(а)</label>
        <div class="error"><?= $errors['contract'] ?? '' ?></div>

        <button type="submit">Сохранить</button>
    </form>
</body>
</html>
