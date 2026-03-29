<?= $this->escape('<script>') ?>
<?= $this->sanitize('<script>console.log("evil");</script><b>clean</b>'); ?>

