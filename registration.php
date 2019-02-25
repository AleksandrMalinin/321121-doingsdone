<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set("Europe/Moscow");
setlocale(LC_ALL, 'ru_RU');

require_once('./functions.php');
require_once('./init.php');

$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;

    $required = ['email', 'password', 'name'];
    $errors = [];

    foreach ($required as $key) {
        if (empty($form[$key])) {
            $errors[$key] = 'Заполните это поле';
        }
    }

    // проверяет валидность адреса почты
    $email = filter_var($form['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $errors['email'] = 'E-mail указан некорректно';
    }

    if (empty($errors)) {
        $meow = filter_var($form['email'], FILTER_VALIDATE_EMAIL);

        $email = mysqli_real_escape_string($connect, $form['email']);
        $sql = "SELECT id FROM users WHERE email = '$email'";
        $result = mysqli_query($connect, $sql);

        // если запрос возвращает результат
        if (mysqli_num_rows($result) > 0) {
            $errors['email'] = 'Пользователь с такой почтой уже зарегистрирован';
        } else {
            $password = password_hash($form['password'], PASSWORD_DEFAULT);

            $sql = 'INSERT INTO users (date_register, email, name, password) VALUES (NOW(), ?, ?, ?)';
            $stmt = db_get_prepare_stmt($connect, $sql, [$form['email'], $form['name'], $password]);
            $result = mysqli_stmt_execute($stmt);
        }

        if ($result && empty($errors)) {
            header('Location: /');
            exit();
        }
    }

    $data = $errors;
}

$page_content = include_template('registration.php', [
    'data' => $data,
    'title' => 'Дела в порядке - Регистрация'
]);

print($page_content);
