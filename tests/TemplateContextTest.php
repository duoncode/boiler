<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract\Escaper as EscaperContract;
use Duon\Boiler\Engine;
use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Proxy\StringProxy;
use Duon\Boiler\Template;
use Duon\Boiler\TemplateContext;
use Duon\Boiler\Wrapper;

final class TemplateContextTest extends TestCase
{
	private string $templates;
	private Template $template;

	protected function setUp(): void
	{
		$this->templates = __DIR__ . '/templates/default/';
		$path = $this->templates . 'simple.php';
		$this->template = new Template($path);
	}

	public function testGetContext(): void
	{
		$tmplContext = new TemplateContext(
			$this->template,
			[
				'value1' => 'Value 1',
				'value2' => '<i>Value 2</i>',
				'value3' => 3,
			],
			[],
			true,
		);
		$context = $tmplContext->context();

		$this->assertInstanceOf(StringProxy::class, $context['value1']);
		$this->assertSame('Value 1', (string) $context['value1']);
		$this->assertInstanceOf(StringProxy::class, $context['value2']);
		$this->assertSame('&lt;i&gt;Value 2&lt;/i&gt;', (string) $context['value2']);
		$this->assertSame(3, $context['value3']);
	}

	public function testWhitelistedObjectMatchesLaterWhitelistEntry(): void
	{
		$value = new Whitelisted();
		$tmplContext = new TemplateContext(
			$this->template,
			['value' => $value],
			[\stdClass::class, WhitelistBase::class],
			true,
		);
		$context = $tmplContext->context();

		$this->assertSame($value, $context['value']);
	}

	public function testAddingToContext(): void
	{
		$tmplContext = new TemplateContext($this->template, ['value1' => 'Value 1'], [], true);
		$value2 = $tmplContext->add('value2', '<i>Value 2</i>');
		$context = $tmplContext->context();

		$this->assertInstanceOf(StringProxy::class, $context['value1']);
		$this->assertSame('Value 1', (string) $context['value1']);
		$this->assertInstanceOf(StringProxy::class, $context['value2']);
		$this->assertSame('&lt;i&gt;Value 2&lt;/i&gt;', (string) $context['value2']);
		$this->assertInstanceOf(StringProxy::class, $value2);
		$this->assertSame('&lt;i&gt;Value 2&lt;/i&gt;', (string) $value2);
	}

	public function testAddingToEscapedContextInvalidatesCachedContext(): void
	{
		$tmplContext = new TemplateContext($this->template, ['value1' => 'Value 1'], [], true);
		$tmplContext->context();
		$tmplContext->add('value2', '<i>Value 2</i>');
		$context = $tmplContext->context();

		$this->assertInstanceOf(StringProxy::class, $context['value2']);
		$this->assertSame('&lt;i&gt;Value 2&lt;/i&gt;', (string) $context['value2']);
	}

	public function testAddingToUnescapedContextReturnsRawValue(): void
	{
		$tmplContext = new TemplateContext($this->template, [], [], false);
		$value = $tmplContext->add('value', '<i>Value</i>');
		$context = $tmplContext->context();

		$this->assertSame('<i>Value</i>', $context['value']);
		$this->assertSame('<i>Value</i>', $value);
	}

	public function testEscapedContextLeavesResourcesRaw(): void
	{
		$resource = tmpfile();
		assert(is_resource($resource), 'tmpfile() must return a valid resource for this test');

		try {
			$tmplContext = new TemplateContext($this->template, ['value' => $resource], [], true);
			$context = $tmplContext->context();

			$this->assertSame($resource, $context['value']);
		} finally {
			fclose($resource);
		}
	}

	public function testEscapesStringableObjects(): void
	{
		$tmplContext = new TemplateContext($this->template, [], [], true);
		$value = new class {
			public function __toString(): string
			{
				return '<b>Value</b>';
			}
		};

		$this->assertSame('&lt;b&gt;Value&lt;/b&gt;', $tmplContext->esc($value));
	}

	public function testEscCanUseExplicitStrategyForStringProxy(): void
	{
		$template = new Template(
			$this->templates . 'simple.php',
			engine: Engine::create(
				$this->templates,
				wrapper: new Wrapper(new class implements EscaperContract {
					public function escape(
						string $value,
						?string $strategy = null,
					): string {
						return match ($strategy) {
							'caps' => strtoupper(htmlspecialchars($value)),
							default => htmlspecialchars($value),
						};
					}
				}),
			),
		);
		$tmplContext = new TemplateContext($template, [], [], true);
		$value = $this->stringProxy('<tag>');

		$this->assertSame('&LT;TAG&GT;', $tmplContext->esc($value, 'caps'));
	}

	public function testEscRejectsNonStringableWrappedObjects(): void
	{
		$this->throws(RuntimeException::class, 'cannot be escaped as string');

		$tmplContext = new TemplateContext($this->template, [], [], true);
		$tmplContext->esc($this->objectProxy(new class {}));
	}
}
