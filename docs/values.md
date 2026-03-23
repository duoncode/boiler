# Displaying values

Boiler wraps strings and objects before it exposes them to escaped templates.
Use `$this->raw($value)` or `->unwrap()` when you need the original value for a
comparison or other explicit logic.

Strings are wrapped as `Duon\Boiler\Proxy\StringProxy`. Non-traversable objects
are wrapped as `Duon\Boiler\Proxy\ObjectProxy`. Arrays and traversables use
specialized array and iterator proxies.

If you pass a `StringProxy` or `ObjectProxy` to `esc`, Boiler unwraps it before
it calls {{php('htmlspecialchars')}}.

## Working with raw values

Use the template helper `raw` when you need the original value instead of the
wrapped proxy:

```php
<?php if ($this->raw($title) !== '') { ?>
	<h1><?= $title ?></h1>
<?php } ?>
```

## Changing the arguments passed to {{php('htmlspecialchars')}}

Boiler passes the flags `ENT_QUOTES | ENT_SUBSTITUTE` and the encoding `UTF-8`
when calling PHP's {{php('htmlspecialchars')}} function internally. If you need
to override these defaults, use the template helper method `esc`:

```php
$this->esc($value, ENT_NOQUOTES | ENT_HTML401, 'EUC-JP');
$this->esc(
	value: $value,
	flags: ENT_NOQUOTES | ENT_HTML401,
	encoding: 'EUC-JP'
);
```
