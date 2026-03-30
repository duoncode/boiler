# The engine

The `Engine` is Boiler's central object and usually the only object you need to instantiate manually. It locates templates on disk and renders them with a shared set of defaults, custom methods, and escape rules.

Assume the following directory structure:

```text
path
`-- to
    |-- templates
    `-- additional
```

## Create an engine

Create an engine with one or more template directories:

```php
$engine = \Duon\Boiler\Engine::create('/path/to/templates');
```

If the directory does not exist, Boiler throws `\Duon\Boiler\Exception\LookupException`.

## Use multiple directories

Pass multiple directories when you want fallback lookup or override behavior:

```php
$engine = \Duon\Boiler\Engine::create([
    '/path/to/templates',
    '/path/to/additional',
]);
```

Boiler searches directories in order. If a template is not found in the first one, it keeps searching the next one.

## Use namespaces

Pass an associative array when you want stable names for specific directories:

```php
$engine = \Duon\Boiler\Engine::create([
    'first' => '/path/to/templates',
    'second' => '/path/to/additional',
]);
```

You can later target a specific directory with `namespace:template`:

```php
$engine->render('second:page');
```

Read [rendering templates](rendering.md) for the lookup rules.

## Add default values

Pass defaults as the second argument when values should be available in every render:

```php
$engine = \Duon\Boiler\Engine::create(
    '/path/to/templates',
    ['siteName' => 'Duon'],
);
```

Per-render context overrides defaults with the same key.

## Whitelist trusted classes

Pass a list of class names as the third argument when specific objects should be left unwrapped in escaped renders:

```php
$engine = \Duon\Boiler\Engine::create(
    '/path/to/templates',
    defaults: [],
    whitelist: [TrustedHtml::class],
);
```

Use this only for values you fully trust. Whitelisted objects bypass Boiler's normal object wrapping and can output unescaped string content from methods such as `__toString()`.

Read [displaying values](values.md) for the escaping model.

## Customize the wrapper

Pass a custom `Wrapper` when you want to replace Boiler's default escaping or provide custom HTML sanitization for `$this->sanitize()`:

```php
use Duon\Boiler\Contract\Sanitizer;
use Duon\Boiler\Wrapper;

final class AppSanitizer implements Sanitizer
{
    public function sanitize(
        string $value,
        ?string $strategy = null,
    ): string {
        return strip_tags($value, '<b><i><a>');
    }
}

$engine = \Duon\Boiler\Engine::create(
    '/path/to/templates',
    wrapper: new Wrapper(sanitizer: new AppSanitizer()),
);
```

`Wrapper` accepts an optional escaper and an optional sanitizer. If `symfony/html-sanitizer` is installed, `Wrapper` uses Boiler's built-in `Sanitizer` automatically. If you call `$this->sanitize()` when no custom or built-in sanitizer is available, Boiler throws `\Duon\Boiler\Exception\MissingSanitizerException`.

When you only need extra named strategies, you can extend Boiler's built-in escaper or sanitizer instead of replacing the whole implementation:

```php
use Duon\Boiler\Contract\EscapeStrategy;
use Duon\Boiler\Contract\SanitizeStrategy;
use Duon\Boiler\Escaper;
use Duon\Boiler\Sanitizer;
use Duon\Boiler\Wrapper;

$escaper = new Escaper(
    strategies: [
        'caps' => new class implements EscapeStrategy {
            public function apply(string $value): string
            {
                return strtoupper(
                    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                );
            }
        },
    ],
);

$sanitizer = new Sanitizer();
$sanitizer->register('text', new class implements SanitizeStrategy {
    public function apply(string $value): string
    {
        return strip_tags($value);
    }
});

$engine = \Duon\Boiler\Engine::create(
    '/path/to/templates',
    wrapper: new Wrapper(escaper: $escaper, sanitizer: $sanitizer),
);
```

## Control escaping

Boiler escapes strings and `Stringable` values by default:

```php
$engine = \Duon\Boiler\Engine::create('/path/to/templates');
$html = $engine->render('page');
```

Create an unescaped engine when automatic escaping should be off by default:

```php
$engine = \Duon\Boiler\Engine::unescaped('/path/to/templates');
$html = $engine->render('page');
```

Override the engine default per render:

```php
$engine = \Duon\Boiler\Engine::create('/path/to/templates');
$engine->renderUnescaped('page');

$engine = \Duon\Boiler\Engine::unescaped('/path/to/templates');
$engine->renderEscaped('page');
```

## Render templates

Render a template by name and optionally pass a context array:

```php
$engine->render('template');
$engine->render('template', ['value1' => 1, 'value2' => 2]);
```

Read [rendering templates](rendering.md) for path syntax, subdirectories, overrides, and namespaces.

## Register custom template methods

Custom methods are available as `$this->methodName()` inside templates:

```php
$engine->registerMethod('upper', function (string $value): string {
    return strtoupper($value);
});
```

Boiler unwraps proxy arguments before it calls your method, so the callable receives normal PHP values instead of proxy objects.

In escaped renders, Boiler wraps the return value again before exposing it to the template. In unescaped renders, it returns the unwrapped value.

## Useful methods

### Check whether a template exists

```php
if ($engine->exists('template')) {
    $engine->render('template');
}
```

### Get the resolved file path for a template

```php
$filePath = $engine->getFile('template');
```

### Get a reusable `Template` instance

```php
$template = $engine->template('template');

assert($template instanceof \Duon\Boiler\Template);
```

A `Template` instance can be rendered multiple times safely.
