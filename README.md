# Boiler

<!-- prettier-ignore-start -->
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/a11454828b7e478b847d2910284b7cf9)](https://app.codacy.com/gh/duonrun/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/a11454828b7e478b847d2910284b7cf9)](https://app.codacy.com/gh/duonrun/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)
[![Psalm level](https://shepherd.dev/github/duonrun/boiler/level.svg?)](https://shepherd.dev/github/duonrun/boiler)
[![Psalm coverage](https://shepherd.dev/github/duonrun/boiler/coverage.svg?)](https://shepherd.dev/github/duonrun/boiler)
<!-- prettier-ignore-end -->

Boiler is a small template engine for PHP 8.5+, inspired by Plates. Like Plates, it uses native PHP as its templating language rather than introducing a custom syntax.

Key differences from Plates:

- Automatic escaping of strings and [Stringable](https://www.php.net/manual/en/class.stringable.php) values for enhanced security
- Global template context, making all variables accessible throughout the template

Other highlights:

- Layouts, inserts/partials, and sections, including append and prepend support
- Wrapper-driven escaping and a pluggable filter system for value transformations
- Custom template methods and optional trusted classes

## Installation

```console
composer require duon/boiler
```

Install Symfony's HTML sanitizer when you want Boiler's built-in `sanitize` filter:

```console
composer require symfony/html-sanitizer
```

## Documentation

Start here: [docs/index.md](docs/index.md).

### Topic overview

- [Quick start](docs/quickstart.md)
- [Engine](docs/engine.md)
- [Rendering templates](docs/rendering.md)
- [Displaying values](docs/values.md)
- [Layouts](docs/layouts.md)
- [Inserts](docs/inserts.md)
- [Sections](docs/sections.md)
- [Template](docs/template.md)

## Quick start

Consider this example directory structure:

```text
path
`-- to
    `-- templates
        `-- page.php
```

Create a template file at `/path/to/templates/page.php` with this content:

```php
<p>ID <?= $id ?></p>
```

Then initialize the `Engine` and render your template:

```php
use Duon\Boiler\Engine;

$engine = Engine::create('/path/to/templates');
$html = $engine->render('page', ['id' => 13]);

assert($html === '<p>ID 13</p>');
```

## Common patterns

Render from multiple directories, optionally with namespaces:

```php
$engine = Engine::create([
    'theme' => '/path/to/theme',
    'app' => '/path/to/templates',
]);

// Renders the first match (theme overrides app)
$engine->render('page');

// Force a specific namespace
$engine->render('app:page');
```

Control escaping:

```php
$engine = Engine::create('/path/to/templates');
$engine->render('page');
$engine->renderUnescaped('page');

$engine = Engine::unescaped('/path/to/templates');
$engine->render('page');
$engine->renderEscaped('page');
```

Configure shared defaults and trusted classes:

```php
$engine = Engine::create(
    '/path/to/templates',
    defaults: ['siteName' => 'Duon'],
    trusted: [TrustedHtml::class],
);
```

Register custom filters with the fluent `filter()` method:

```php
use Duon\Boiler\Contract\Filter;

$engine = Engine::create('/path/to/templates')
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

Filters are available as virtual methods on wrapped string values in templates. In escaped renders, Boiler wraps string values for you. When you need filters on a raw value inside a template, call `$this->wrap($value)` first. Boiler ships with built-in `sanitize` and `strip` filters. If `symfony/html-sanitizer` is installed, the `sanitize` filter is registered automatically.

When you need custom lookup, wrapper, filter, or escaper infrastructure, instantiate `Engine` directly with your own resolver and `Duon\Boiler\Environment`. Use `Environment::setWrapper()` when you want to replace Boiler's runtime wrapper entirely. Use `Environment::setFilters()` and `Environment::setEscapers()` when you want Boiler to keep building the wrapper internally. These modes are mutually exclusive, and the environment is sealed on first `wrapper()` or render.

When Boiler manages filters or escapers internally, you can register additional named entries with `Engine::filter()` and `Engine::escape()`.

Filter lookups use `Duon\Boiler\Contract\Filters`, which only needs a `get(string $name): Duon\Boiler\Contract\Filter` method.
Filter registration is exposed separately through `Duon\Boiler\Contract\RegistersFilters`.
Escaper lookups use `Duon\Boiler\Contract\Escapers`, which expose `default` and `get(string $name): Duon\Boiler\Contract\Escaper`.
Escaper registration is exposed separately through `Duon\Boiler\Contract\RegistersEscapers`.

Template helpers available via `$this` inside templates:

- `$this->layout('layout')`
- `$this->insert('partial', ['value' => '...'])`
- `$this->begin('name')` / `$this->append('name')` / `$this->prepend('name')` / `$this->end()`
- `$this->section('name', 'default')` / `$this->has('name')`
- `$this->unwrap($value)` when you need the original value instead of the escaped wrapper
- `$this->escape($value)` and `$this->wrap($value)` when you need proxy behavior such as string filters on a raw value

## Error handling

Boiler fails fast when template lookup or render state is invalid. Common cases include:

- missing template directories
- missing templates or unknown namespaces
- invalid template names such as malformed `namespace:template` paths
- path traversal outside configured template roots
- assigning more than one layout in the same template
- nested or unclosed section capture blocks
- calling an unknown filter or custom template method

See [rendering templates](docs/rendering.md), [layouts](docs/layouts.md), [sections](docs/sections.md), and [template](docs/template.md) for the relevant rules.

## Benchmark

Boiler includes a canonical benchmark in [`bench/`](bench/) that renders a feature-rich catalog page with layouts, repeated partials, sections or blocks, mixed array, object, and iterator view data, loops, and escaping.

The benchmark is meant to resemble a realistic steady-state page render and is used mainly to catch performance regressions during development.

Run the benchmark with Xdebug disabled. Xdebug adds substantial runtime overhead, especially for Boiler's proxy-based auto escaping, so results with Xdebug enabled are not useful for fair engine comparisons. `composer benchmark` already runs it with `xdebug.mode=off`.

Results depend on PHP version, OPcache settings, hardware, and workload shape. They do not represent every rendering scenario and should not be treated as universal rankings. If you want to evaluate Boiler for your environment, run the benchmark locally and compare it with your own templates.

## Run the tests

```console
composer test
composer lint
composer types
composer mdlint
```

For the full verification pipeline, run:

```console
composer ci
```

## License

This project is licensed under the [MIT license](LICENSE.md).
