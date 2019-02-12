<?php
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
        if ($item['category'] === $project) {
            $tasks_quantity++;
        }
    }

    return $tasks_quantity;
}
