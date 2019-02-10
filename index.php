<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./functions.php');
require_once('./data.php');

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

$page_content = include_template('index.php', ['show_complete_tasks' => $show_complete_tasks, 'tasks' => $tasks]);

$layout_content = include_template('layout.php', [
    'projects' => $projects,
    'tasks' => $tasks,
	'content' => $page_content,
	'user' => 'Константин',
	'title' => 'Дела в порядке - Главная страница'
]);

print($layout_content);
