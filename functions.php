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

/**
 * Подключает шаблон
 * @param string $name имя шаблона
 * @param array $data массив передаваемых данных
 * @return string
 */
function include_template($name, $data) {
    $name = 'templates/' . $name;

    if (!is_readable($name)) {
        return '<div class="error-message">Template is not found</div>';
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * Определяет срочность задачи
 * @param string $task_deadline_str дата дедлайна
 * @param integer $status статус задачи (0 - невыполненная, 1 - выполненная)
 * @return bool
 */
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

/**
 * Проверяет существование проекта
 * @param mysqli $connect подключение к бд
 * @param integer $user_id уникальный id пользователя
 * @param string|integer $project id или название проекта
 * @return bool
 */
function is_project($connect, $user_id, $project_id) {
    $bool = false;
    $sql_project = "SELECT * FROM projects WHERE user_id = ? AND ";
    $sql_id = 'id = ?';
    $sql_name = 'name = ?';

    // проверяет что задача ссылается на существующий проект
    $sql_project = is_int($project_id) ? $sql_project . $sql_id : $sql_project . $sql_name;
    $project = get_data($connect, $sql_project, [$user_id, $project_id]);

    if ($project) {
        $bool = true;
    }

    return $bool;
}

/**
 * Проверяет существование электронной почты
 * @param mysqli $connect подключение к бд
 * @param string $email уникальный email пользователя
 * @return bool
 */
function is_email($connect, $email) {
    $bool = false;
    $email_escaped = mysqli_real_escape_string($connect, $email);
    $sql = "SELECT id FROM users WHERE email = '$email_escaped'";
    $result = mysqli_query($connect, $sql);

    if (mysqli_num_rows($result) > 0) {
        $bool = true;
    }

    return $bool;
}

/**
 * Проверяет существование пользователя
 * @param mysqli $connect подключение к бд
 * @param string $email уникальный email пользователя
 * @return mysqli_result
 */
function check_user_existence($connect, $email) {
    $email_escaped = mysqli_real_escape_string($connect, $email);
    $sql = "SELECT * FROM users WHERE email = '$email_escaped'";
    $result = mysqli_query($connect, $sql);

    return $result;
}

/**
 * Получает массив данных
 * @param mysqli $connect подключение к бд
 * @param $sql $sql sql запрос
 * @param array $initial_data массив c данными
 * @param bool $bool параметр определяющий тип получаемых данных (однострочный или многострочный)
 * @return array
 */
function get_data($connect, $sql, $initial_data = [], $bool = true) {
    if (!$connect) {
        $error = mysqli_connect_error();
        print('Connection error: ' . $error);
    } else {
        $stmt = db_get_prepare_stmt($connect, $sql, $initial_data);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result) {
            $error = mysqli_error($connect);
            print('MYSQL error: ' . $error);
        } else {
            $data = check_multiline_data($bool, $result);
        }
    }

    return $data;
}

/**
 * Определяет функцию, которая будет использована для получения данных в зависимости от их типа
 * @param bool $bool параметр определяющий тип получаемых данных (однострочный или многострочный)
 * @param string $result результат из подготовленного запроса
 * @return array
 */
function check_multiline_data($bool, $result) {
    $data = $bool ? mysqli_fetch_all($result, MYSQLI_ASSOC) : mysqli_fetch_assoc($result);

    return $data;
}

/**
 * Получает данные по проектам
 * @param mysqli $connect подключение к бд
 * @param string $user уникальный id пользователя
 * @param array $quantity массив с количеством задач
 * @return array
 */
function get_projects_data($connect, $user, $quantity) {
    $sql_projects = 'SELECT * FROM projects WHERE user_id = ?';

    $initial_projects = get_data($connect, $sql_projects, [$user]);
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
                'link' => $initial_projects[$i]['id']
            ];

            // собираем массив с проектами
            $projects[] = $project;
        }
    }

    return $projects;
}

/**
 * Получает данные по задачам, определяет тип для вывода (выполненная / невыполненная)
 * @param mysqli $connect подключение к бд
 * @param string $user уникальный id пользователя
 * @param integer $status статус задачи (0 - невыполненная, 1 - выполненная)
 * @param bool|string|integer $project_id id или название проекта
 * @param bool|string $deadline дата дедлайна
 * @param bool|string $search поисковый запрос
 * @return array
 */
function get_tasks_data($connect, $user, $status, $project_id = false, $deadline = false, $search = false) {
    $data[] = $user;
    // начальный запрос
    $sql_tasks = 'SELECT * FROM tasks WHERE user_id = ?';

    // запрос если передан id проекта и это число
    if ($project_id && is_int($project_id)) {
        $sql_project_id = ' AND project_id = ?';
        $sql_tasks .= $sql_project_id;
        $data[] = $project_id;
    }

    // запрос для типа 'Входящие'
    if ($project_id === 'incoming') {
        // запрос для проектов которых нет в базе
        $sql_tasks .= ' AND project_id IS NULL';
    }

    // запрос для выполненых задач
    if (!$status) {
        // запрос для статуса задачи
        $sql_tasks .= ' AND status = ' . $status;
    }

    // запрос для поиска по имени задачи
    if ($search) {
        // запрос для поиска по имени задачи
        $sql_tasks .= ' AND MATCH(name) AGAINST(?)';
        $data[] = $search;
    }

    // для фильтрации по датам
    switch ($deadline) {
        // сегодняшние
        case 'today':
            $deadline = date('Y-m-d 23:59:59');
            $sql_tasks .= " AND date_deadline = '$deadline'";
            break;

        // завтрашние
        case 'tommorow':
            $deadline = date('Y-m-d 23:59:59', time() + 86400);
            $sql_tasks .= " AND date_deadline = '$deadline'";
            break;

        // просроченные
        case 'past':
            $deadline = date('Y-m-d 23:59:59');
            $sql_tasks .= " AND date_deadline < '$deadline'";
            break;

        // все
        default:
            break;
    }

    return get_data($connect, $sql_tasks, $data);
}

/**
 * Получает количество задач
 * @param mysqli $connect подключение к бд
 * @param string $user уникальный id пользователя
 * @param null|string $project id вкладок проектов ВСЕ и ВХОДЯЩИЕ
 * @param bool|integer $bool статус задачи (0 - невыполненная, 1 - выполненная)
 * @return array
 */
function get_tasks_quantity($connect, $user, $project = NULL, $bool = false) {
    $sql_tasks = 'SELECT COUNT(*) FROM tasks WHERE user_id = ?';
    $sql_null = ' AND project_id IS NULL';
    $sql_undone = ' AND status = 0';
    $sql_group_by = ' AND project_id IS NOT NULL GROUP BY project_id';

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
            $bool = true;
            break;
    }

    return get_data($connect, $sql_tasks, [$user], $bool);
}

/**
 * Получает данные пользователей
 * @param mysqli $connect подключение к бд
 * @param string $user уникальный id пользователя
 * @return array
 */
function get_users_data($connect, $user) {
    $sql_users = 'SELECT name FROM users WHERE id = ?';

    return get_data($connect, $sql_users, [$user], false);
}

/**
 * Добавляет новую задачу в бд
 * @param mysqli $connect подключение к бд
 * @param string $task название задачи
 * @param string $user уникальный id пользователя
 * @param null|string $deadline дата дедлайна
 * @param null|integer $project id проекта
 * @param null|string $file ссылка на загруженный файл
 * @return void
 */
function add_task($connect, $task, $user, $deadline = NULL, $project = NULL, $file = NULL) {
    $sql = 'INSERT INTO tasks (name, user_id, date_deadline, project_id, file) VALUES (?, ?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connect, $sql, [$task, $user, $deadline, $project, $file]);
    mysqli_stmt_execute($stmt);
}

/**
 * Добавляет новый проект
 * @param mysqli $connect подключение в бд
 * @param string $project название проекта
 * @param string $user уникальный id пользователя
 * @return void
 */
function add_project($connect, $project, $user) {
    $sql = 'INSERT INTO projects (name, user_id) VALUES (?, ?)';

    $stmt = db_get_prepare_stmt($connect, $sql, [$project, $user]);
    mysqli_stmt_execute($stmt);
}

/**
 * Добавляет нового пользователя в бд
 * @param mysqli $connect подключение к бд
 * @param string $email email пользователя
 * @param string $name имя пользователя
 * @param string $password захэшированный пароль пользователя
 * @return bool
 */
function add_user($connect, $email, $name, $password) {
    $sql = 'INSERT INTO users (email, name, password) VALUES (?, ?, ?)';

    $stmt = db_get_prepare_stmt($connect, $sql, [$email, $name, $password]);
    $result = mysqli_stmt_execute($stmt);

    return $result;
}

/**
 * Меняет статус задачи
 * @param mysqli $connect подключение к бд
 * @param string $user уникальный id пользователя
 * @param string $task_id id задачи
 * @param integer $status статус задачи (0 - невыполненная, 1 - выполненная)
 * @return void
 */
function change_task_status($connect, $user, $task_id, $status) {
    $status = $status ? 0 : 1;
    $sql = 'UPDATE tasks SET status = ' . $status . ' WHERE user_id = ? AND id = ?';

    $stmt = db_get_prepare_stmt($connect, $sql, [$user, $task_id]);
    mysqli_stmt_execute($stmt);
}

/**
 * Генерирует url
 * @param array $array массив GET запросов
 * @param string $key_current текущий ключ запроса
 * @return string
 */
function generate_url($array, $key_current) {
    $str = '';

    foreach ($array as $key => $value) {
        // если ключи совпадают
        if ($key !== $key_current) {
            $str .= $key . '=' . $value . '&';
        }
    }

    // обрезаем последний символ (&)
    $str = substr($str, 0, -1);

    return $str;
}

/**
 * Получает данные (список срочных задач, почту и имя пользователя) для оповещения пользователя о предстоящих задачах
 * @param mysqli $connect подключение к бд
 * @param array $data пустой массив
 * @return array
 */
function get_data_for_notify($connect, $data) {
    $sql = 'SELECT GROUP_CONCAT(t.name SEPARATOR ", ") list, u.email, u.name AS user_name
            FROM tasks t JOIN users u ON t.user_id = u.id
            WHERE DAY(t.date_deadline) = DAY(NOW())
            AND t.status = 0
            GROUP BY u.email';

    return get_data($connect, $sql, $data);
}
