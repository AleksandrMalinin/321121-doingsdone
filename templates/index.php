<h2 class="content__main-heading">Список задач</h2>

<form class="search-form" action="index.php" method="post">
    <input class="search-form__input" type="text" name="" value="" placeholder="Поиск по задачам">
    <input class="search-form__submit" type="submit" name="" value="Искать">
</form>

<div class="tasks-controls">
    <nav class="tasks-switch">
        <a href="/" class="tasks-switch__item tasks-switch__item--active">Все задачи</a>
        <a href="/" class="tasks-switch__item">Повестка дня</a>
        <a href="/" class="tasks-switch__item">Завтра</a>
        <a href="/" class="tasks-switch__item">Просроченные</a>
    </nav>

    <label class="checkbox">
        <a href="/index.php?<?= generate_url($_GET, 'show_completed'); ?><?php if (!isset($_GET['show_completed'])): ?>&show_completed=is_checked<?php endif ?>">
            <input class="checkbox__input visually-hidden show_completed" type="checkbox"
            <?php if (isset($_GET['show_completed'])): ?>checked<?php endif ?>>
            <span class="checkbox__text">Показывать выполненные</span>
        </a>
    </label>
</div>

<table class="tasks">
    <?php foreach ($tasks as $task): ?>
        <?=include_template('task.php', ['task' => $task]); ?>
    <?php endforeach ?>
</table>
