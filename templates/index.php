<h2 class="content__main-heading">Список задач</h2>

<form class="search-form" action="/index.php" method="get">
    <input class="search-form__input" type="text" name="tasks_search" value="" placeholder="Поиск по задачам">
    <input class="search-form__submit" type="submit" name="" value="Искать">
</form>

<div class="tasks-controls">
    <nav class="tasks-switch">
        <?php
            $term = $_GET['term'] ?? NULL;
            $show_completed = $_GET['show_completed'] ?? NULL;
        ?>

        <a href="/" class="tasks-switch__item <?php if(!isset($_GET['term'])): ?>tasks-switch__item--active<?php endif ?>">Все задачи</a>

        <a href="/index.php?<?= generate_url($_GET, 'term', 'today'); ?>&term=today"
            class="tasks-switch__item <?php if ($term === 'today'): ?>tasks-switch__item--active<?php endif ?>">Повестка дня</a>

        <a href="/index.php?<?= generate_url($_GET, 'term', 'tommorow'); ?>&term=tommorow"
            class="tasks-switch__item <?php if ($term === 'tommorow'): ?>tasks-switch__item--active<?php endif ?>">Завтра</a>

        <a href="/index.php?<?= generate_url($_GET, 'term', 'past'); ?>&term=past"
            class="tasks-switch__item <?php if ($term === 'past'): ?>tasks-switch__item--active<?php endif ?>">Просроченные</a>
    </nav>

    <label class="checkbox">
        <a href="/index.php?<?= generate_url($_GET, 'show_completed', 'is_checked'); ?><?php if (empty($show_completed)): ?>&show_completed=is_checked<?php endif ?>">
            <input class="checkbox__input visually-hidden show_completed" type="checkbox"
            <?php if ($show_completed): ?>checked<?php endif ?>>
            <span class="checkbox__text">Показывать выполненные</span>
        </a>
    </label>
</div>

<table class="tasks">
    <?php foreach ($tasks as $task): ?>
        <?=include_template('task.php', ['task' => $task]); ?>
    <?php endforeach ?>
</table>
