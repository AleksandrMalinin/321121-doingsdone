<li class="main-navigation__list-item <?php if (isset($_GET['id']) && intval($_GET['id']) === $project['id']): ?>main-navigation__list-item--active<?php endif ?>">
    <a class="main-navigation__list-item-link" href="/index.php?id=<?= $project['link']; ?>&<?= generate_url($_GET, 'id'); ?>">
        <?= strip_tags($project['name']); ?>
    </a>
    <span class="main-navigation__list-item-count"><?= $project['tasks_count']; ?></span>
</li>
