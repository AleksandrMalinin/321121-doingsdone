<?php
require_once('./functions.php');
require_once('./init.php');

// Текущий юзер
$user_id = 3;

// Получаем массив с количеством невыполненных заданий
$tasks_quantity = get_tasks_quantity_data($connect, $user_id);

// Получаем массив проектов
$projects = get_projects_data($connect, $user_id, $tasks_quantity);

// Передаём массив с проектами в шаблон
$page_content = include_template('add.php', [
    'projects' => $projects
]);

print($page_content);
