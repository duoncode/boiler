# Layouts

Use layouts when multiple templates share a common outer structure.
A template can assign one layout, and layouts can themselves use another
layout.

Assume the following directory structure:

```text
path
`-- to
    `-- templates
        |-- page.php
        |-- outer.php
        `-- inner.php
```

## Assign a layout

Inside a template, call `$this->layout()` before outputting the layout content:

```php
<?php $this->layout('inner') ?>

<p><?= $text ?></p>
```

Create `inner.php`:

```php
<body>
    <?= $content ?>
    <footer><?= $text ?></footer>
</body>
```

Render the page:

```php
$engine->render('page', ['text' => 'Boiler']);
```

This produces:

```html
<body>
    <p>Boiler</p>
    <footer>Boiler</footer>
</body>
```

The layout receives the rendered page content in `$content`. It also receives
all values from the page template context by default.

## Override layout context

Pass a second argument when the layout should receive extra values or override
existing ones:

```php
<?php $this->layout('inner', ['text' => 'Changed']) ?>
```

The layout now sees `Changed` for `$text`, while the page template still sees
its original value.

## Stack layouts

Layouts can assign another layout:

```php
<?php $this->layout('outer') ?>

<div class="inner">
    <?= $content ?>
</div>
```

Create `outer.php`:

```php
<body>
    <main>
        <?= $content ?>
    </main>
</body>
```

Boiler renders layouts from the innermost template outward.

## Rules

- A template can set only one layout. Calling `$this->layout()` twice raises a
  runtime error.
- Layout lookup follows the same rules as normal template rendering, including
  namespaces and directory overrides.
- Standalone `Template` instances resolve layouts relative to the directory of
  the template file.
