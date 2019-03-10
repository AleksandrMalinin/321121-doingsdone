<?php
require_once('./functions.php');
require_once('./init.php');

$data = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;
	$required = ['email', 'password'];
    $result = NULL;

    foreach ($required as $key) {
        if (empty($form[$key])) {
            $errors[$key] = 'Заполните это поле';
        }
    }

    // если поле непустое
    if (!empty($form['email'])) {
        // проверяет валидность адреса почты
        $email = filter_var($form['email'], FILTER_VALIDATE_EMAIL);

        // если почта невалидна
        if (!$email) {
            $errors['email'] = 'E-mail указан некорректно';
        } else {
            $result = check_user_existence($connect, $email);
            $user = $result ? mysqli_fetch_array($result, MYSQLI_ASSOC) : NULL;
        }
    }

    if ($user) {
        if (password_verify($form['password'], $user['password'])) {
            $_SESSION['user'] = $user;
        } else {
            $errors['password'] = 'Неверный пароль';
        }
    } else {
        if (empty($errors)) {
            $errors['email'] = 'Такой пользователь не найден';
        }
    }

    if ($result && empty($errors)) {
        header("Location: /");
        exit();
    }

    $data = $form;
}

$page_content = include_template('auth.php', [
    'form' => $data,
    'errors' => $errors,
    'title' => 'Дела в порядке - Авторизация на сайте'
]);

print($page_content);
