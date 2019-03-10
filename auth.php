<?php
require_once('./functions.php');
require_once('./init.php');

$data = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;
	$required = ['email', 'password'];

    foreach ($required as $key) {
        if (empty($form[$key])) {
            $form_errors[$key] = 'Заполните это поле';
        }
    }

    // если поле непустое
    if (!empty($form['email'])) {
        // проверяет валидность адреса почты
        $email = filter_var($form['email'], FILTER_VALIDATE_EMAIL);
        $result = NULL;

        // если почта невалидна
        if (!$email) {
            $form_errors['email'] = 'E-mail указан некорректно';
        } else {
            $result = check_user_existence($connect, $email);
            $user = $result ? mysqli_fetch_array($result, MYSQLI_ASSOC) : NULL;
        }
    }

    if ($user) {
        if (password_verify($form['password'], $user['password'])) {
            $_SESSION['user'] = $user;
        } else {
            $form_errors['password'] = 'Неверный пароль';
        }
    } else {
        if (empty($form_errors)) {
            $form_errors['email'] = 'Такой пользователь не найден';
        }
    }

    if ($result && empty($form_errors)) {
        header("Location: /");
        exit();
    }

    $errors = $form_errors;
    $data = $form;
}

$page_content = include_template('auth.php', [
    'form' => $data,
    'errors' => $errors,
    'title' => 'Дела в порядке - Авторизация на сайте'
]);

print($page_content);
