<li class="main-navigation__list-item">
    <a class="main-navigation__list-item-link" href="#"><?= strip_tags($project['name']); ?></a>
    <span class="main-navigation__list-item-count"><?= count_tasks_quantity($tasks, $project['id']); ?></span>
</li>
