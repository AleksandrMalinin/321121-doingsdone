<tr class="tasks__item task <?php if ($task['done']): ?>task--completed<?php endif ?>">
    <td class="task__select">
        <label class="checkbox task__checkbox">
            <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="1" <?php if ($task['done']): ?>checked<?php endif ?>>
            <span class="checkbox__text"><?= filter_content($task['title']); ?></span>
        </label>
    </td>

    <td class="task__file">
        <a class="download-link" href="#">Home.psd</a>
    </td>

    <td class="task__date"><?= filter_content($task['date']); ?></td>
</tr>
