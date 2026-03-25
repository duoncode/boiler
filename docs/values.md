# Displaying values

In escaped renders, Boiler wraps strings and most objects before exposing them
to templates. This gives you automatic escaping while still allowing objects,
arrays, and iterators to be used naturally in template code.

Read this page if you want to understand when Boiler escapes values and when you
need `$this->unwrap()`, `$this->esc()`, or `$this->clean()`.

## What Boiler escapes automatically

In escaped renders, Boiler escapes:

- strings
- `Stringable` values
- strings returned from wrapped objects, arrays, and iterators during template
  access

Boiler uses PHP's `htmlspecialchars()` with these defaults:

- `ENT_QUOTES | ENT_SUBSTITUTE`
- `UTF-8`

Integers, floats, booleans, `null`, resources, and similar scalar values are not
converted into escaped string wrappers ahead of time.

## Unwrap values

Use `$this->unwrap($value)` when you need the original value instead of the
wrapped proxy object.

This is mainly useful for explicit checks or when you need the original array of
arguments inside your own helper logic.

```php
<?php if ($this->unwrap($title) !== '') { ?>
    <h1><?= $title ?></h1>
<?php } ?>
```

## Escape a value explicitly

Use `$this->esc()` when you need to escape a value manually, or when you want to
override the default flags or encoding:

```php
$this->esc($value, ENT_NOQUOTES | ENT_HTML401, 'EUC-JP');
$this->esc(
    value: $value,
    flags: ENT_NOQUOTES | ENT_HTML401,
    encoding: 'EUC-JP',
);
```

`$this->esc()` accepts strings, `Stringable` values, and Boiler's wrapped string
or object proxies.

## Sanitize HTML

Use `$this->clean()` when you want to allow a safe subset of HTML instead of
escaping everything:

```php
<?= $this->clean($html) ?>
```

Boiler uses `symfony/html-sanitizer` under the hood. You can also pass a custom
`HtmlSanitizerConfig`:

```php
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

$config = (new HtmlSanitizerConfig())->allowElement('b');

echo $this->clean($html, $config);
```

Use `clean()` for trusted formatting scenarios where you want to keep some HTML.
Use normal escaped output or `$this->esc()` when plain text output is enough.

## Trusted class whitelist

By default, Boiler wraps objects in escaped renders. If a specific class should
stay unwrapped, add it to the whitelist when creating the `Engine` or when
rendering a standalone `Template`.

```php
$engine = \Duon\Boiler\Engine::create(
    '/path/to/templates',
    defaults: [],
    whitelist: [TrustedHtml::class],
);
```

Use this carefully. Whitelisted objects bypass Boiler's normal wrapping and can
output unescaped string content.

## Working with arrays, iterators, and objects

Boiler also wraps arrays, traversables, and objects so nested values keep the
same escaping behavior inside templates.

That means this stays escaped in a normal render:

```php
<?php foreach ($items as $item) { ?>
    <li><?= $item ?></li>
<?php } ?>
```

The same applies when values come from object properties, object methods, or
iterator items.

## Unescaped renders

When you use `Engine::unescaped()` or `renderUnescaped()`, Boiler stops wrapping
values for automatic escaping.

In that mode:

- `<?= $value ?>` outputs unescaped string content
- `$this->unwrap()` usually returns the same value you already have
- `$this->clean()` is still available when you want sanitization
