<?php
session_start();

// Подключение к БД
$host = 'localhost';
$dbname = 'u69168';
$username = 'u69168';
$password = '2021936';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

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
    $errors['phone'] = validate_field($data['phone'], "/^\\+?[0-9\-\s]+$/", "Телефон должен содержать только цифры, пробелы, дефис и знак '+'.");
    $errors['email'] = validate_field($data['email'], "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/", "Некорректный формат e-mail.");
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
        // Сохранение в БД
        $stmt = $conn->prepare("INSERT INTO application (fio, phone, email, dob, gender, bio) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $data['fio'], $data['phone'], $data['email'], $data['dob'], $data['gender'], $data['bio']);
        $stmt->execute();
        $app_id = $conn->insert_id;
        $stmt->close();

        // Сохранение языков программирования
        $stmt = $conn->prepare("INSERT INTO application_languages (app_id, lang_id) VALUES (?, ?)");
        foreach ($data['languages'] as $lang) {
            $stmt->bind_param("is", $app_id, $lang);
            $stmt->execute();
        }
        $stmt->close();

        // Сохраняем корректные данные в Cookies на 1 год
        foreach ($data as $key => $value) {
            setcookie($key, is_array($value) ? json_encode($value) : $value, time() + 31536000, '/');
        }
        // Удаляем ошибки
        setcookie('errors', '', time() - 3600, '/');
        setcookie('form_data', '', time() - 3600, '/');
        header("Location: index.php?save=1");
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
