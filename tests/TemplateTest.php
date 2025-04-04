<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Exception\LookupException;
use Duon\Boiler\Exception\RenderException;
use Duon\Boiler\Proxy\ValueProxy;
use Duon\Boiler\Template;
use Duon\Boiler\Tests\TestCase;
use Duon\Boiler\Tests\WhitelistBase;
use Duon\Boiler\Tests\Whitelisted;
use ParseError;

final class TemplateTest extends TestCase
{
	private string $templates;

	protected function setUp(): void
	{
		$this->templates = __DIR__ . '/templates/default/';
	}

	public function testStandaloneRendering(): void
	{
		$path = $this->templates . 'simple.php';
		$template = new Template($path);

		$this->assertSame(
			'<h1>boiler</h1><p>rocks</p>',
			$this->fullTrim($template->render([
				'obj' => $this->obj(),
				'text' => 'rocks',
			])),
		);
	}

	public function testValueWhitelisting(): void
	{
		$path = $this->templates . 'whitelist.php';
		$template = new Template($path);

		$this->assertSame(
			'<h1>headline</h1><p>test</p>',
			$this->fullTrim($template->render(
				[
					'wl' => new Whitelisted(),
					'content' => 'test',
				],
				[Whitelisted::class],
			)),
		);
	}

	public function testWhitelistingWithBaseClass(): void
	{
		$path = $this->templates . 'whitelist.php';
		$template = new Template($path);

		$this->assertSame(
			'<h1>headline</h1><p>test</p>',
			$this->fullTrim($template->render(
				[
					'wl' => new Whitelisted(),
					'content' => 'test',
				],
				[WhitelistBase::class],
			)),
		);
	}

	public function testNotWhitelisted(): void
	{
		$path = $this->templates . 'whitelist.php';
		$template = new Template($path);

		$this->assertSame(
			'&lt;h1&gt;headline&lt;/h1&gt;&lt;p&gt;test&lt;/p&gt;',
			$this->fullTrim($template->render(
				[
					'wl' => new Whitelisted(),
					'content' => 'test',
				],
			)),
		);
	}

	public function testStandaloneWithLayout(): void
	{
		$path = $this->templates . 'uselayout.php';
		$template = new Template($path);

		$this->assertSame(
			'<body><p>standalone</p><p>standalone</p></body>',
			$this->fullTrim($template->render(['text' => 'standalone'])),
		);
	}

	public function testOverwriteLayoutContext(): void
	{
		$template = new Template($this->templates . 'overridelayoutcontext.php');

		$this->assertSame(
			'<body><p>Boiler 1</p><p>Boiler 2</p><p>changed</p><p>Boiler 2</p></body>',
			$this->fullTrim($template->render([
				'text' => 'Boiler 1',
				'text2' => 'Boiler 2',
			])),
		);
	}

	public function testNonExistentLayoutWithoutExtension(): void
	{
		$this->throws(LookupException::class, 'Template not found:.*doesnotexist');

		$template = new Template($this->templates . 'nonexistentlayout.php');

		$template->render();
	}

	public function testNonExistentLayoutWithExtension(): void
	{
		$this->throws(LookupException::class, 'Template not found:.*doesnotexist.php');

		$template = new Template($this->templates . 'nonexistentlayoutext.php');

		$template->render();
	}

	public function testCustomTemplateMethod(): void
	{
		$template = new Template($this->templates . 'method.php');
		$template->registerMethod('upper', function (ValueProxy $value): ValueProxy {
			return new ValueProxy(strtoupper($value->unwrap()));
		});

		$this->assertSame('<h2>BOILER</h2>', $this->fullTrim($template->render([ 'text' => 'Boiler', ])));
	}

	public function testNonExistentTemplateWithoutExtension(): void
	{
		$this->throws(LookupException::class, 'Template not found');

		$template = new Template($this->templates . 'nonexistent');

		$template->render();
	}

	public function testDirectoryNotFound(): void
	{
		$this->throws(LookupException::class, 'Template directory does not exist');

		$template = new Template('/__nonexistent_boiler_dir__/template.php');

		$template->render();
	}

	public function testEmptyPath(): void
	{
		$this->throws(LookupException::class, 'No directory given or');

		$template = new Template('');

		$template->render();
	}

	public function testRenderError(): void
	{
		$this->throws(RenderException::class, 'Template rendering error');

		$template = new Template($this->templates . 'rendererror.php');

		$template->render();
	}
}

