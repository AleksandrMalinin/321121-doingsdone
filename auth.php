<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set("Europe/Moscow");
setlocale(LC_ALL, 'ru_RU');

require_once('./functions.php');
require_once('./init.php');

$data = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;

	$required = ['email', 'password'];

    foreach ($required as $key) {
        if (empty($form[$key])) {
            $errors[$key] = 'Заполните это поле';
        }
    }

    // если поле непустое
    if (!empty($form['email'])) {
        // проверяет валидность адреса почты
        $email = filter_var($form['email'], FILTER_VALIDATE_EMAIL);

        if (!$email) {
            $errors['email'] = 'E-mail указан некорректно';
        }
    }

    if (empty($errors)) {
        $result = is_user($connect, $form['email']);
        $user = $result ? mysqli_fetch_array($result, MYSQLI_ASSOC) : NULL;

        if ($user) {
            if (password_verify($form['password'], $user['password'])) {
    			$_SESSION['user'] = $user;
            } else {
    			$errors['password'] = 'Неверный пароль';
    		}
    	} else {
    		$errors['email'] = 'Такой пользователь не найден';
    	}

        if ($result && empty($errors)) {
            var_dump($_SESSION['user']);
            var_dump($user);
            header("Location: /");
            exit();
        }
    }

    $errors = $errors;
    $data = $form;
}

$page_content = include_template('auth.php', [
    'form' => $data,
    'errors' => $errors,
    'title' => 'Дела в порядке - Авторизация на сайте'
]);

print($page_content);
