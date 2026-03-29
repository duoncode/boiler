# Template

Use `Template` when you want to render a single template file directly instead of resolving it through an `Engine`.

## Create a standalone template

Pass the full file path to the constructor:

```php
$template = new \Duon\Boiler\Template('/path/to/templates/page.php');
```

Boiler creates an internal `Engine` automatically and uses the directory that contains the file as the template root.

## Render the template

Use the same render methods as on `Engine`:

```php
$html = $template->render(['id' => 13]);
$html = $template->renderEscaped(['id' => 13]);
$html = $template->renderUnescaped(['id' => 13]);
```

## Layouts and inserts

Standalone templates can use the same composition helpers as engine-backed renders:

- `$this->layout('layout')`
- `$this->insert('partial')`
- sections via `$this->begin()` and `$this->section()`

Those template references are resolved relative to the directory that contains the original template file.

## Whitelisting trusted classes

When you render a standalone template, pass the whitelist directly to the render call:

```php
$html = $template->render(
    ['value' => new TrustedHtml()],
    [TrustedHtml::class],
);
```

## Reuse a template instance

A `Template` instance can be rendered multiple times safely:

```php
$template = new \Duon\Boiler\Template('/path/to/templates/page.php');

$first = $template->render(['id' => 1]);
$second = $template->render(['id' => 2]);
```

Boiler resets per-render state such as assigned layouts and captured sections between renders.

## Error handling

- Boiler raises `LookupException` when the template file or its directory does not exist.
- Boiler raises `LookupException` when a standalone layout or insert cannot be resolved relative to the template directory.
- Boiler raises `RenderException` when the template itself throws during render, for example because of a parse error or runtime error inside the template.
