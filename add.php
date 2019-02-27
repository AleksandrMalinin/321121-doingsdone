<?php
require_once('./functions.php');
require_once('./init.php');

if (empty($user)) {
    http_response_code(401);
    die();
}

$tasks_incoming = 0;

// Получаем имя текущего пользователя
$users = get_users_data($connect, $user_id);

// Получаем массив с количеством невыполненных заданий
$tasks_quantity = get_tasks_quantity($connect, $user_id);

// Получаем массив проектов
$projects = get_projects_data($connect, $user_id, $tasks_quantity);

// Получаем общее количество задач (Все)
$all_tasks = get_tasks_quantity($connect, $user_id, 'all');

// Получаем количество задач без проектов (Входящие)
$random_tasks = get_tasks_quantity($connect, $user_id, 'incoming');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;

    $required = ['name'];
    $errors = [];

    foreach ($required as $key) {
		if (empty($_POST[$key])) {
            $errors[$key] = 'Заполните это поле';
		}
	}

    $deadline = NULL;
    // если дата установлена
    if (!empty($form['date'])) {
        $date = check_date_format($form['date']);

        if (!$date) {
            $errors['date'] = 'Заполните поле в указанном формате';
        } else if (strtotime($form['date']) < time()) {
            $errors['date'] = 'Дата должна быть больше текущей';
        } else {
            $deadline = date('Y.m.d 23:59:59', strtotime($form['date']));
        }
    }

    $project_id = NULL;
    if ($form['project'] !== 'incoming') {
        // проверяет что задача ссылается на существующий проект
        $project = is_project($connect, $user_id, intval($form['project']));

        if ($project) {
            $project_id = intval($form['project']);
        } else {
            $errors['project'] = 'Такого проекта не существует';
        }
    }

    $file = NULL;
    // проверяет загружен ли файл, даёт ему новое имя, помещает в корень
    if (is_uploaded_file($_FILES['preview']['tmp_name'])) {
        $tmp_name = $_FILES['preview']['tmp_name'];
        $file = $_FILES['preview']['name'];
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $file = uniqid() . "." . $extension;
        move_uploaded_file($tmp_name, '' . $file);
    }

    if (count($errors)) {
    	$page_content = include_template('add.php', [
            'errors' => $errors,
            'form' => $form,
            'projects' => $projects,
            'incoming' => $random_tasks['COUNT(*)'],
            'tasks_all' => $all_tasks['COUNT(*)'],
            'user' => $users['name'],
            'title' => 'Дела в порядке - Добавление задачи'
        ]);
    } else {
        add_task($connect, $form['name'], $user_id, $deadline, $project_id, $file);
        header("Location: /");
    }
} else {
    // Передаём массив с проектами в шаблон
    $page_content = include_template('add.php', [
        'projects' => $projects,
        'incoming' => $random_tasks['COUNT(*)'],
        'tasks_all' => $all_tasks['COUNT(*)'],
        'user' => $users['name'],
        'title' => 'Дела в порядке - Добавление задачи'
    ]);
}

print($page_content);
