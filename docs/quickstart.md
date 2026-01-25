# Quick Start

Install Boiler via Composer:

```shell
composer require duon/boiler
```

Then create a directory where your PHP templates reside.  
Assuming the following directory structure ...

```text
path
`-- to
	`-- templates
```

... create the file `/path/to/templates/page.php` with the content:

```php
<p>ID <?= $id ?></p>
```

Now create an `Engine` instance and render the template:

```php
use Duon\Boiler\Engine;

$engine = Engine::create('/path/to/templates');
$html = $engine->render('page', ['id' => 13]);

assert($html == '<p>ID 13</p>');
```
