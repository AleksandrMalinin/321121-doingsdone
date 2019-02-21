<?php
require_once('./mysql_helper.php');

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

// получает массив данных
function get_data($connect, $sql, $user = [], $bool) {
    $data = null;

    if (!$connect) {
        $error = mysqli_connect_error();
        print('Connection error: ' . $error);
    } else {
        $stmt = db_get_prepare_stmt($connect, $sql, [$user]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result) {
            $error = mysqli_error($connect);
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
        $data = mysqli_fetch_row($result);
    }

    return $data;
}

// делает запрос для проектов
function make_projects_request() {
    return $sql_projects = 'SELECT * FROM projects WHERE user_id = ?';
}

// делает запрос для задач
function make_tasks_request() {
    return $sql_tasks = 'SELECT * FROM tasks WHERE user_id = ?';
}

// делает запрос для юзеров
function make_users_request() {
    return $sql_users = 'SELECT name FROM users WHERE id = ?';
}

// делает запрос для количества невыполненных задач по каждому проекту
function make_tasks_quantity_request() {
    return $sql_tasks_quantity = 'SELECT COUNT(*) FROM tasks t JOIN projects p ON p.id = t.project_id WHERE t.status = 0 && t.user_id = ? GROUP BY project_id';
}
