<section class="facet-group">
    <h4><?= $this->e($facet['title'], 'trim|strtoupper') ?></h4>

    <?php if ($facet['expanded']): ?>
        <ul>
            <?php foreach ($facet['options'] as $option): ?>
                <li class="<?= $option['selected'] ? 'selected' : 'idle' ?>">
                    <span><?= $this->e($option['label'], 'trim') ?></span>
                    <small>(<?= $option['count'] ?>)</small>
                </li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <p>Collapsed</p>
    <?php endif ?>
</section>
