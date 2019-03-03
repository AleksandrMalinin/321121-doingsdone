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
function is_project($connect, $user_id, $project) {
    $sql_project = 'SELECT * FROM projects WHERE user_id = ? AND ';
    $sql_id = "id = '$project'";
    $sql_name = "name = '$project'";

    // проверяет что задача ссылается на существующий проект
    if (is_int($project)) {
        $sql_project .= $sql_id;
    } else {
        $sql_project .= $sql_name;
    }

    $project = get_data($connect, $sql_project, $user_id);

    return $project;
}

// проверяет на существование email // TODO: Объединить в одну функцию
function is_email($connect, $email) {
    $email_escaped = mysqli_real_escape_string($connect, $email);
    $sql = "SELECT id FROM users WHERE email = '$email_escaped'";
    $result = mysqli_query($connect, $sql);

    return $result;
}

// проверяет на существование юзера // TODO: Объединить в одну функцию
function is_user($connect, $email) {
    $email_escaped = mysqli_real_escape_string($connect, $email);
    $sql = "SELECT * FROM users WHERE email = '$email_escaped'";
    $result = mysqli_query($connect, $sql);

    return $result;
}

// получает массив данных
function get_data($connect, $sql, $user = [], $bool = true) {
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
        $data = mysqli_fetch_assoc($result);
    }

    return $data;
}

// делает запрос для проектов
function get_projects_data($connect, $user, $quantity) {
    $sql_projects = 'SELECT * FROM projects WHERE user_id = ?';

    $initial_projects = get_data($connect, $sql_projects, $user);
    $projects = [];

    // если у юзера есть проекты
    if (!empty($initial_projects)) {
        // собираем ассоциативный массив каждого проекта
        for ($i = 0; $i < count($initial_projects); $i++) {
            $tasks_count = $quantity[$i]['COUNT(*)'] ?? 0;

            $project = [
                'id' => $initial_projects[$i]['id'],
                'name' => $initial_projects[$i]['name'],
                'tasks_count' => $tasks_count,
                'link' => '/index.php?id=' . $initial_projects[$i]['id']
            ];

            // собираем массив с проектами
            $projects[] = $project;
        }
    }

    return $projects;
}

// делает запрос для задач, определяет тип для вывода (выполненная / невыполненная)
function get_tasks_data($connect, $user, $bool, $id = false) {
    $additional_condition = ' AND status = ' . $bool;
    $null_condition = ' AND project_id IS NULL';
    $sql_tasks = 'SELECT * FROM tasks WHERE user_id = ?';

    if ($id && is_int($id)) {
        $sql_project_id = ' AND project_id = ' . $id;
        $sql_tasks .= $sql_project_id;
    }

    if ($id === 'incoming') {
        $sql_tasks .= $null_condition;
    }

    if ($id === 'all' && $bool) {
        $sql_tasks .= $additional_condition;
    }

    if (!$bool) {
        $sql_tasks .= $additional_condition;
    }

    return get_data($connect, $sql_tasks, $user);
}

// получает количество задач
function get_tasks_quantity($connect, $user, $project = null) {
    $sql_tasks = 'SELECT COUNT(*) FROM tasks WHERE user_id = ?';
    $sql_null = ' && project_id IS NULL';
    $sql_undone = ' && status = 0';
    $sql_group_by = ' && project_id IS NOT NULL GROUP BY project_id';

    switch ($project) {
        // общее количество невыполненных
        case 'all':
            $sql_tasks .= $sql_undone;
            break;

        // без проекта
        case 'incoming':
            $sql_tasks .= $sql_undone . $sql_null;
            break;

        // невыполненных по каждому проекту
        default:
            $sql_tasks .= $sql_undone . $sql_group_by;
            return get_data($connect, $sql_tasks, $user);
            break;
    }

    return get_data($connect, $sql_tasks, $user, false);
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

// добавляет новый проект
function add_project($connect, $project, $user) {
    $sql = 'INSERT INTO projects (name, user_id) VALUES (?, ?)';

    $stmt = db_get_prepare_stmt($connect, $sql, [$project, $user]);
    mysqli_stmt_execute($stmt);
}

// добавляет нового юзера
function add_user($connect, $email, $name, $password) {
    $sql = 'INSERT INTO users (date_register, email, name, password) VALUES (NOW(), ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connect, $sql, [$email, $name, $password]);
    $result = mysqli_stmt_execute($stmt);

    return $result;
}

// меняет статус задачи
function change_task_status($connect, $task_id, $task_status) {
    if ($task_status) {
        $task_status = 0;
    } else {
        $task_status = 1;
    }

    $sql = 'UPDATE tasks SET status = ' . $task_status . ' WHERE id = ?';

    $stmt = db_get_prepare_stmt($connect, $sql, [$task_id]);
    mysqli_stmt_execute($stmt);
}

// генерирует url
function generate_url ($array) {
    $show = '&show_completed=is_checked';

    foreach ($array as $key => $value) {
        $str = $key . '=' . $value;
    }

    if (!isset($array['show_completed'])) {
        $str .= $show;
    }

    return $str;
}
