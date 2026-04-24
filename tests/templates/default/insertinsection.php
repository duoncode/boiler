<?php $this->layout('readsection'); ?>
<p><?= $text; ?></p>
<?php $this->begin('list'); ?>
<ul>
    <?php $this->insert('sectionitem', ['text' => $text]); ?>
</ul>
<?php $this->end(); ?>
