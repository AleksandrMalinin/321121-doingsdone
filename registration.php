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
        $result = is_email($connect, $form['email']);

        // если запрос возвращает результат
        if (mysqli_num_rows($result) > 0) {
            $errors['email'] = 'Пользователь с такой почтой уже зарегистрирован';
        } else {
            $password = password_hash($form['password'], PASSWORD_DEFAULT);

            $result = add_user($connect, $form['email'], $form['name'], $password);
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
