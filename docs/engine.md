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

Pass a custom `Wrapper` when you want to replace Boiler's default escaping:

```php
use Duon\Boiler\Contract\Escaper;
use Duon\Boiler\Escapers;
use Duon\Boiler\Wrapper;

$engine = \Duon\Boiler\Engine::create(
    '/path/to/templates',
    wrapper: new Wrapper(new Escapers([
        Escapers::HTML => new class implements Escaper {
            public function escape(string $value): string
            {
                return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
        },
    ])),
);
```

`Contract\Wrapper` only covers wrapping, unwrapping, escaping, and filter lookup. Boiler's built-in `Wrapper` also implements `Contract\FilterRegister`, so `Engine::filter()` can register new filters on it. If you provide your own wrapper and want to keep using `Engine::filter()`, implement `Contract\FilterRegister` too.

`Wrapper` accepts an optional escaper registry and optional pre-registered filters. Boiler's built-in `Escapers` registry includes the `html` escaper, exposes its constructor-configured default via `default`, and supports constructor-seeded entries plus incremental `->register()` calls when you only need extra named escapers instead of a full wrapper replacement:

```php
use Duon\Boiler\Contract\Escaper;
use Duon\Boiler\Escapers;
use Duon\Boiler\Wrapper;

$escapers = new Escapers([
    'caps' => new class implements Escaper {
        public function escape(string $value): string
        {
            return strtoupper(
                htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            );
        }
    },
], default: 'caps');

$engine = \Duon\Boiler\Engine::create(
    '/path/to/templates',
    wrapper: new Wrapper(escapers: $escapers),
);
```

You can also register another named escaper after construction:

```php
$escapers = new Escapers();

$escapers->register('caps', new class implements Escaper {
    public function escape(string $value): string
    {
        return strtoupper(
            htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }
});

$engine = \Duon\Boiler\Engine::create(
    '/path/to/templates',
    wrapper: new Wrapper(escapers: $escapers),
);
```

## Register filters

Filters are value transformations you can apply to template values. Register filters on the engine with the fluent `filter()` method:

```php
use Duon\Boiler\Contract\Filter;

$engine = \Duon\Boiler\Engine::create('/path/to/templates')
    ->filter('upper', new class implements Filter {
        public function apply(string $value, mixed ...$args): string
        {
            return strtoupper($value);
        }

        public function safe(): bool
        {
            return false;
        }
    });
```

`Engine::filter()` requires a wrapper that implements `Contract\FilterRegister`. Boiler's built-in `Wrapper` already does.

A filter implements `Duon\Boiler\Contract\Filter` with two methods:

- `apply(string $value, mixed ...$args): string` transforms the value.
- `safe(): bool` returns `true` when the filter output is safe HTML and should skip auto-escaping.

Filters are available as virtual methods on wrapped string values in templates:

```php
<?= $title->upper() ?>
<?= $html->sanitize() ?>
<?= $body->strip('<b>') ?>
```

In escaped renders, Boiler wraps string values for you. When you need filters on a raw value or in an unescaped render, call `$this->wrap($value)` first:

```php
<?= $this->wrap($html)->sanitize() ?>
```

Filters can be chained. Once a safe filter is applied, the chain stays safe:

```php
<?= $html->sanitize()->strip('<b>') ?>
```

Boiler ships with two built-in filters:

- `sanitize` removes unsafe HTML (requires `symfony/html-sanitizer`). This filter is safe.
- `strip` removes HTML tags via `strip_tags()`. This filter is not safe.

Read [displaying values](values.md) for more on filters and escaping.

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
