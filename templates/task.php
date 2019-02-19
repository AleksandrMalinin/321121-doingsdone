<tr class="tasks__item task
    <?php if ($task['status']): ?>task--completed<?php endif ?>
    <?php if (check_urgency($task['date_deadline'], $task['status'])): ?>task--important<?php endif ?>"
>
    <td class="task__select">
        <label class="checkbox task__checkbox">
            <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="1" <?php if ($task['status']): ?>checked<?php endif ?>>
            <span class="checkbox__text"><?= strip_tags($task['name']); ?></span>
        </label>
    </td>

    <td class="task__file">
        <a class="download-link" href="#">Home.psd</a>
    </td>

    <td class="task__date"><?= strip_tags($task['date_deadline']); ?></td>
</tr>
