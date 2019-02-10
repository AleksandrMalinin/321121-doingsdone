<tr class="tasks__item task
    <?php if ($task['done']): ?>task--completed<?php endif ?>
    <?php if (check_urgency($task['date'])): ?>task--important<?php endif ?>"
>
    <td class="task__select">
        <label class="checkbox task__checkbox">
            <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="1" <?php if ($task['done']): ?>checked<?php endif ?>>
            <span class="checkbox__text"><?= filterContent($task['title']); ?></span>
        </label>
    </td>

    <td class="task__file">
        <a class="download-link" href="#">Home.psd</a>
    </td>

    <td class="task__date"><?= filterContent($task['date']); ?></td>
</tr>
