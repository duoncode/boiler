# Boiler

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/a11454828b7e478b847d2910284b7cf9)](https://app.codacy.com/gh/duonrun/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/a11454828b7e478b847d2910284b7cf9)](https://app.codacy.com/gh/duonrun/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)
[![Psalm level](https://shepherd.dev/github/duonrun/boiler/level.svg?)](https://shepherd.dev/github/duonrun/boiler)
[![Psalm coverage](https://shepherd.dev/github/duonrun/boiler/coverage.svg?)](https://shepherd.dev/github/duonrun/boiler)

Boiler is a small template engine for PHP 8.5+, inspired by Plates.
Like Plates, it uses native PHP as its templating language rather than
introducing a custom syntax.

Key differences from Plates:

- Automatic escaping of strings and
  [Stringable](https://www.php.net/manual/en/class.stringable.php) values for
  enhanced security
- Global template context, making all variables accessible throughout the
  template

Other highlights:

- Layouts, inserts/partials, and sections, including append and prepend support
- Optional HTML sanitization via `symfony/html-sanitizer`
- Custom template methods and optional whitelisting of trusted value classes

## Installation

```console
composer require duon/boiler
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

Template helpers available via `$this` inside templates:

- `$this->layout('layout')`
- `$this->insert('partial', ['value' => '...'])`
- `$this->begin('name')` / `$this->append('name')` / `$this->prepend('name')` /
  `$this->end()`
- `$this->section('name', 'default')` / `$this->has('name')`
- `$this->unwrap($value)` when you need the original value instead of the escaped wrapper
- `$this->esc($value)` and `$this->clean($html)`

## Error handling

Boiler fails fast when template lookup or render state is invalid.
Common cases include:

- missing template directories
- missing templates or unknown namespaces
- invalid template names such as malformed `namespace:template` paths
- path traversal outside configured template roots
- assigning more than one layout in the same template
- nested or unclosed section capture blocks
- calling an unknown custom template method

See [rendering templates](docs/rendering.md), [layouts](docs/layouts.md),
[sections](docs/sections.md), and [template](docs/template.md) for the relevant
rules.

## Benchmark

Boiler includes a canonical benchmark in [`bench/`](bench/) that renders a
feature-rich catalog page with layouts, repeated partials, sections or blocks,
mixed array, object, and iterator view data, loops, and escaping.

The benchmark is meant to resemble a realistic steady-state page render and is
used mainly to catch performance regressions during development.

Results depend on PHP version, OPcache settings, hardware, and workload shape.
They do not represent every rendering scenario and should not be treated as
universal rankings. If you want to evaluate Boiler for your environment, run the
benchmark locally and compare it with your own templates.

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
