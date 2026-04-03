<?= $this->escape('<script>') ?>
<?= $this->filter('sanitize', '<script>console.log("evil");</script><b>clean</b>'); ?>

