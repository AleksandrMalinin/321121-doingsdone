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
function get_projects_data($connect, $user) {
    $sql_projects = 'SELECT * FROM projects WHERE user_id = ?';

    return get_data($connect, $sql_projects, $user);
}

// делает запрос для задач, определяет тип для вывода (выполненная / невыполненная)
function get_tasks_data($connect, $user, $bool, $id = false) {
    $additional_condition = ' AND status = ' . $bool;
    $sql_tasks = 'SELECT * FROM tasks WHERE user_id = ?';

    if ($id) {
        $sql_project_id = ' AND project_id = ' . $id;
        $sql_tasks .= $sql_project_id;
    }

    if (!$bool) {
        $sql_tasks .= $additional_condition;
    }

    return get_data($connect, $sql_tasks, $user);
}

// делает запрос для юзеров
function get_users_data($connect, $user) {
    $sql_users = 'SELECT name FROM users WHERE id = ?';

    return get_data($connect, $sql_users, $user, false);
}

// делает запрос для количества невыполненных задач по каждому проекту
function get_tasks_quantity_data($connect, $user) {
    $sql_tasks_quantity = 'SELECT COUNT(*) FROM tasks WHERE status = 0 && user_id = ? GROUP BY project_id';

    return get_data($connect, $sql_tasks_quantity, $user);
}
