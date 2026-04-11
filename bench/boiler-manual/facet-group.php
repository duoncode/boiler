<section class="facet-group">
    <h4><?= $this->escape($this->wrap($facet['title'])->trim()->upper()) ?></h4>

    <?php if ($facet['expanded']): ?>
        <ul>
            <?php foreach ($facet['options'] as $option): ?>
                <li class="<?= $option['selected'] ? 'selected' : 'idle' ?>">
                    <span><?= $this->escape(trim($option['label'])) ?></span>
                    <small>(<?= $option['count'] ?>)</small>
                </li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <p>Collapsed</p>
    <?php endif ?>
</section>
