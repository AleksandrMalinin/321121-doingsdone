<?php
require_once('./mysql_helper.php');

/**
 * Проверяет, что переданная дата соответствует формату ДД.ММ.ГГГГ
 * @param string $date строка с датой
 * @return bool
 */
function check_date_format($date) {
    $result = false;
    $regexp = '/(\d{2})\.(\d{2})\.(\d{4})/m';
    if (preg_match($regexp, $date, $parts) && count($parts) == 4) {
        $result = checkdate($parts[2], $parts[1], $parts[3]);
    }
    return $result;
}

function include_template($name, $data) {
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return '<div class="error-message">Template is not found</div>';
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

// проверяет задачу на срочность
function check_urgency($task_deadline_str, $status) {
    $urgency = false;

    // проверяет на наличие даты и статус задачи
    if ($task_deadline_str && !$status) {
        // текущий timestamp
        $now_ts = time();

        // timestamp для дедлайна
        $task_deadline_ts = strtotime($task_deadline_str);
        $time_diff = $task_deadline_ts - $now_ts;
        $hours_to_task_deadline = floor($time_diff / 60 / 60);

        if ($hours_to_task_deadline <= 24) {
            $urgency = true;
        }
    }

    return $urgency;
}

// проверяет на существование проекта
function is_project($connect, $user_id, $project_id) {
    // проверяет что задача ссылается на существующий проект
    $sql_project = 'SELECT * FROM projects WHERE user_id = ? AND id = ' . $project_id;
    $project = get_data($connect, $sql_project, $user_id);

    return $project;
}

// проверяет на существование email
function is_email($connect, $email) {
    $email_escaped = mysqli_real_escape_string($connect, $email);
    $sql = "SELECT id FROM users WHERE email = '$email_escaped'";
    $result = mysqli_query($connect, $sql);

    return $result;
}

// получает массив данных
function get_data($con, $sql, $user = [], $bool = true) {
    $data = null;

    if (!$con) {
        $error = mysqli_connect_error();
        print('Connection error: ' . $error);
    } else {
        $stmt = db_get_prepare_stmt($con, $sql, [$user]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result) {
            $error = mysqli_error($con);
            print('MYSQL error: ' . $error);
        } else {
            $data = check_multiline_data($bool, $data, $result);
        }
    }

    return $data;
}

// подбирает функцию в зависимости от того многострочные даннные или нет
function check_multiline_data($bool, $data, $result) {
    if ($bool) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $data = mysqli_fetch_assoc($result);
    }

    return $data;
}

// делает запрос для проектов
function get_projects_data($connect, $user, $quantity) {
    $sql_projects = 'SELECT * FROM projects WHERE user_id = ?';

    $initial_projects = get_data($connect, $sql_projects, $user);

    // собираем ассоциативный массив каждого проекта
    for ($i = 0; $i < count($initial_projects); $i++) {
        $tasks_count = $quantity[$i];

        $project = [
            'id' => $initial_projects[$i]['id'],
            'name' => $initial_projects[$i]['name'],
            'tasks_count' => $tasks_count['COUNT(*)'],
            'link' => '/index.php?id=' . $initial_projects[$i]['id']
        ];

        // собираем массив с проектами
        $projects[] = $project;
    }

    return $projects;
}

// делает запрос для задач, определяет тип для вывода (выполненная / невыполненная)
function get_tasks_data($connect, $user, $bool, $id = false) {
    $additional_condition = ' AND status = ' . $bool;
    $null_condition = ' AND project_id IS NULL';
    $sql_tasks = 'SELECT * FROM tasks WHERE user_id = ?';

    if ($id && !is_null($id)) {
        $sql_project_id = ' AND project_id = ' . $id;
        $sql_tasks .= $sql_project_id;
    }

    if (is_null($id)) {
        $sql_tasks .= $null_condition;
    }

    if (!$bool) {
        $sql_tasks .= $additional_condition;
    }

    return get_data($connect, $sql_tasks, $user);
}

// получает количество невыполненных задач по каждому проекту
function get_tasks_quantity_data($connect, $user) {
    $sql_tasks_quantity = 'SELECT COUNT(*) FROM tasks WHERE status = 0 && user_id = ? GROUP BY project_id';

    return get_data($connect, $sql_tasks_quantity, $user);
}

// получает общее количество невыполненных задач
function get_all_tasks_quantity($connect, $user) {
    $sql_tasks = 'SELECT COUNT(*) FROM tasks WHERE status = 0 && user_id = ?';

    return get_data($connect, $sql_tasks, $user, false);
}

// получает количество задач без проекта
function get_random_tasks_quantity($connect, $user) {
    $sql_tasks_quantity = 'SELECT COUNT(*) FROM tasks WHERE user_id = ? && project_id IS NULL';

    return get_data($connect, $sql_tasks_quantity, $user, false);
}

// делает запрос для юзеров
function get_users_data($connect, $user) {
    $sql_users = 'SELECT name FROM users WHERE id = ?';

    return get_data($connect, $sql_users, $user, false);
}


// добавляет новую задачу
function add_task($connect, $task, $user, $deadline = NULL, $project = NULL, $file = NULL) {
    $sql = 'INSERT INTO tasks (name, status, user_id, date_deadline, project_id, file) VALUES (?, 0, ?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connect, $sql, [$task, $user, $deadline, $project, $file]);
    mysqli_stmt_execute($stmt);
}

function add_user($connect, $email, $name, $password) {
    $sql = 'INSERT INTO users (date_register, email, name, password) VALUES (NOW(), ?, ?, ?)';
    $stmt = db_get_prepare_stmt($connect, $sql, [$email, $name, $password]);
    $result = mysqli_stmt_execute($stmt);

    return $result;
}
