<section class="facet-group">
    <h4><?= $facet['title']->trim()->upper() ?></h4>

    <?php if ($facet['expanded']): ?>
        <ul>
            <?php foreach ($facet['options'] as $option): ?>
                <li class="<?= $option['selected'] ? 'selected' : 'idle' ?>">
                    <span><?= $option['label']->trim() ?></span>
                    <small>(<?= $option['count'] ?>)</small>
                </li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <p>Collapsed</p>
    <?php endif ?>
</section>
