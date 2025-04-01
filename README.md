# Boiler

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/4c82c13a91064b58ad709772a12b85bf)](https://app.codacy.com/gh/duoncode/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/4c82c13a91064b58ad709772a12b85bf)](https://app.codacy.com/gh/duoncode/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)
[![Psalm level](https://shepherd.dev/github/duoncode/boiler/level.svg?)](https://shepherd.dev/github/duoncode/boiler)
[![Psalm coverage](https://shepherd.dev/github/duoncode/boiler/coverage.svg?)](https://shepherd.dev/github/duoncode/boiler)

> [!WARNING]  
> This template engine is under active development, so some of the
> features listed and parts of the documentation may be still experimental,
> subject to change, or missing.

Boiler is a template engine for PHP 8.2 and above, inspired by Plates.
Like Plates, it uses native PHP as its templating language rather than
introducing a custom syntax. 

Key differences from Plates:

- Automatic escaping of strings and
  [Stringable](https://www.php.net/manual/en/class.stringable.php) values for
  enhanced security
- Global template context, making all variables accessible throughout the
  template

## Installation

```console
composer require duon/boiler
```

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

$engine = new Engine('/path/to/templates');
$html = $engine->render('page', ['id' => 13]);

assert($html == '<p>ID 13</p>');
```

## Run the tests

```console
phpunit --testdox && \
	psalm --no-cache --show-info=true && \
	phpcs -s -p --ignore=tests/templates src tests
```

## License

Boiler is available under the [MIT license](LICENSE.md).

Copyright © 2022-2024 ebene fünf GmbH. All rights reserved.
