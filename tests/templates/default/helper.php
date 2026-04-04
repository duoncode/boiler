<?= $this->escape('<script>') ?>
<?= $this->wrap('<script>console.log("evil");</script><b>clean</b>')->sanitize(); ?>
