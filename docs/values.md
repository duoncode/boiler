# Displaying values

In escaped renders, Boiler wraps strings and most objects before exposing them to templates. This gives you automatic escaping while still allowing objects, arrays, and iterators to be used naturally in template code.

Read this page if you want to understand when Boiler escapes values and when you need `$this->unwrap()`, `$this->escape()`, or `$this->filter()`.

## What Boiler escapes automatically

In escaped renders, Boiler escapes:

- strings
- `Stringable` values
- strings returned from wrapped objects, arrays, and iterators during template access

Boiler uses PHP's `htmlspecialchars()` with these defaults:

- `ENT_QUOTES | ENT_SUBSTITUTE`
- `UTF-8`

Integers, floats, booleans, `null`, resources, and similar scalar values are not converted into escaped string wrappers ahead of time.

## Unwrap values

Use `$this->unwrap($value)` when you need the original value instead of the wrapped proxy object.

This is mainly useful for explicit checks or when you need the original array of arguments inside your own helper logic.

```php
<?php if ($this->unwrap($title) !== '') : ?>
    <h1><?= $title ?></h1>
<?php endif; ?>
```

## Escape a value explicitly

Use `$this->escape()` when you need to escape a value manually, or when you want to select a named escape strategy:

```php
$this->escape($value);
$this->escape($value, 'html');
$this->escape(
    value: $value,
    strategy: 'html',
);
```

Boiler ships with the `html` strategy. It uses PHP's `htmlspecialchars()` with `ENT_QUOTES | ENT_SUBSTITUTE` and `UTF-8`.

`$this->escape()` accepts strings, `Stringable` values, and Boiler's wrapped string or object proxies. The `strategy` argument is forwarded to the wrapper's configured escaper. Boiler's built-in `Escaper` supports constructor-seeded strategies and incremental `->register()` calls, and custom escaper implementations can expose additional strategy names too.

## Filters

Filters are value transformations applied as virtual methods on string values inside templates:

```php
<?= $html->sanitize() ?>
<?= $title->strip('<b>') ?>
```

Filters can be chained. Once a safe filter is applied in a chain, the result stays safe and skips auto-escaping:

```php
<?= $html->sanitize()->strip('<b>') ?>
```

You can also apply filters from the template context with `$this->filter()`:

```php
<?= $this->filter('sanitize', $html) ?>
<?= $this->filter('strip', $body, '<b><i>') ?>
```

`$this->filter()` accepts strings, `Stringable` values, and Boiler's wrapped string or object proxies.

Boiler ships with two built-in filters:

- `sanitize` removes unsafe HTML while allowing safe elements. This filter is safe, meaning its output skips auto-escaping. Requires `symfony/html-sanitizer`.
- `strip` removes HTML tags via `strip_tags()`. This filter is not safe, so its output is still auto-escaped.

Register custom filters on the engine with the fluent `filter()` method. Read [the engine](engine.md) for details.

Use filters for trusted formatting scenarios where you want to transform values. Use normal escaped output or `$this->escape()` when plain text output is enough.

## Trusted class whitelist

By default, Boiler wraps objects in escaped renders. If a specific class should stay unwrapped, add it to the whitelist when creating the `Engine` or when rendering a standalone `Template`.

```php
$engine = \Duon\Boiler\Engine::create(
    '/path/to/templates',
    defaults: [],
    whitelist: [TrustedHtml::class],
);
```

Use this carefully. Whitelisted objects bypass Boiler's normal wrapping and can output unescaped string content.

## Working with arrays, iterators, and objects

Boiler also wraps arrays, traversables, and objects so nested values keep the same escaping behavior inside templates.

That means this stays escaped in a normal render:

```php
<?php foreach ($items as $item) : ?>
    <li><?= $item ?></li>
<?php endforeach; ?>
```

The same applies when values come from object properties, object methods, or iterator items.

## Unescaped renders

When you use `Engine::unescaped()` or `renderUnescaped()`, Boiler stops wrapping values for automatic escaping.

In that mode:

- `<?= $value ?>` outputs unescaped string content
- `$this->unwrap()` usually returns the same value you already have
- `$this->filter()` and string filters like `->sanitize()` are still available when you want to transform values
