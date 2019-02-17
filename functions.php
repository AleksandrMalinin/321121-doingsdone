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

function count_tasks_quantity($list, $project) {
    $tasks_quantity = 0;

    foreach ($list as $item) {
        if ($item['project_id'] === $project) {
            $tasks_quantity++;
        }
    }

    return $tasks_quantity;
}

function check_urgency($str) {
    $urgency = false;

    // проверяет на наличие даты
    if ($str) {
        $task_deadline_str = $str;

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

function get_data($connect, $sql, $user = []) {
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
            $data = mysqli_fetch_all($res);
        }
    }

    return $data;
}
