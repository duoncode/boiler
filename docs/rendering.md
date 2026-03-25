# Rendering templates

After you create an [`Engine`](engine.md), you render templates with
`render()`, `renderEscaped()`, or `renderUnescaped()`.

Assume the following directory structure:

```text
path
`-- to
    |-- templates
    |   |-- subdir
    |   |   `-- subtemplate.php
    |   |-- blog.php
    |   `-- page.php
    |
    `-- theme
        |-- blog.php
        `-- additional.php
```

And assume this engine setup:

```php
$engine = \Duon\Boiler\Engine::create(
    [
        'theme' => '/path/to/theme',
        'templates' => '/path/to/templates',
    ],
    defaults: [
        'titleSuffix' => ' - Boiler Template Engine',
    ],
);
```

## Render by template name

Reference `page.php` as `page` or `page.php`:

```php
$html = $engine->render('page');
$html = $engine->render('page.php');
```

If you use a different file extension, include it explicitly:

```php
$engine->render('page.tpl');
```

## Pass values to templates

Provide template values as an associative array:

```php
$html = $engine->render('page', [
    'title' => 'The title',
    'content' => 'The content of the page.',
]);
```

If `page.php` contains:

```php
<body>
    <h1><?= $title ?></h1>
    <div><?= $content ?></div>
</body>
```

The rendered output is:

```html
<body>
    <h1>The title</h1>
    <div>The content of the page.</div>
</body>
```

## Render templates in subdirectories

Use forward slashes inside the template name:

```php
$html = $engine->render('subdir/subtemplate', ['value' => 13]);
```

## Directory overrides

If multiple template directories contain the same template, Boiler uses the
first match. In the setup above, both `theme` and `templates` contain `blog.php`.
Because `theme` comes first, it wins:

```php
// renders /path/to/theme/blog.php
$engine->render('blog', ['value' => 13]);
```

This makes it easy to implement themes or application-level overrides for a
shared template set.

## Namespaced paths

Use `namespace:template` when you want a template from a specific directory:

```php
$html = $engine->render('templates:blog', ['value' => 13]);
$html = $engine->render('templates:subdir/subtemplate', ['value' => 13]);
```

If the namespace does not exist, Boiler throws `LookupException`.

## Path validation

Boiler validates template names before lookup:

- empty template names are rejected
- invalid namespace formats such as `foo:bar:baz` are rejected
- path traversal outside the configured template root is rejected

This applies to normal renders and to helper methods such as `$this->layout()`
and `$this->insert()`.

## Escape mode per render

Use the engine default with `render()`:

```php
$html = $engine->render('page', ['title' => '<b>Unsafe</b>']);
```

Force the mode per render when needed:

```php
$html = $engine->renderEscaped('page', ['title' => '<b>Unsafe</b>']);
$html = $engine->renderUnescaped('page', ['title' => '<b>Unsafe</b>']);
```

Read [displaying values](values.md) for details on what Boiler escapes.
