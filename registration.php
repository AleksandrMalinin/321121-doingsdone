<?php
require_once('./functions.php');
require_once('./init.php');

$data = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;
    $required = ['email', 'password', 'name'];

    foreach ($required as $key) {
        if (empty($form[$key])) {
            $form_errors[$key] = 'Заполните это поле';
        }
    }

    // проверяет валидность адреса почты
    $email = filter_var($form['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $form_errors['email'] = 'E-mail указан некорректно';
    }

    if (empty($form_errors)) {
        $result = is_email($connect, $form['email']);

        // если запрос возвращает результат
        if ($result) {
            $form_errors['email'] = 'Пользователь с такой почтой уже зарегистрирован';
        } else {
            $password = password_hash($form['password'], PASSWORD_DEFAULT);
            $result = add_user($connect, $form['email'], $form['name'], $password);
        }

        if ($result && empty($form_errors)) {
            header('Location: /');
            exit();
        }
    }

    $errors = $form_errors;
    $data = $form;
}

$page_content = include_template('registration.php', [
    'errors' => $errors,
    'form' => $data,
    'title' => 'Дела в порядке - Регистрация'
]);

print($page_content);
