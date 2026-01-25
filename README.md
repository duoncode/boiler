# Boiler

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/4c82c13a91064b58ad709772a12b85bf)](https://app.codacy.com/gh/duoncode/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/4c82c13a91064b58ad709772a12b85bf)](https://app.codacy.com/gh/duoncode/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)
[![Psalm level](https://shepherd.dev/github/duoncode/boiler/level.svg?)](https://shepherd.dev/github/duoncode/boiler)
[![Psalm coverage](https://shepherd.dev/github/duoncode/boiler/coverage.svg?)](https://shepherd.dev/github/duoncode/boiler)

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

- Layouts, inserts/partials, and sections (with append/prepend)
- Optional HTML sanitization via `symfony/html-sanitizer`
- Custom template methods and optional whitelisting of trusted value classes

## Installation

```console
composer require duon/boiler
```

## Documentation

Start here: `docs/index.md`.

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

assert($html == '<p>ID 13</p>');
```

## Common patterns

Render from multiple directories (optionally with namespaces):

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
- `$this->esc($value)` and `$this->clean($html)`

## Run the tests

```console
composer test
composer check
composer lint
```

## License

Boiler is available under the [MIT license](LICENSE.md).

Copyright © 2022-2026 ebene fünf GmbH. All rights reserved.
