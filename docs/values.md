# Displaying Values

If you pass a value of type `Duon\Boiler\Proxy\ValueProxy` to `esc` it will
automatically be unwrapped before it is passed to {{php('htmlspecialchars')}}.

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
