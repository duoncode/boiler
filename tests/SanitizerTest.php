<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract;
use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Sanitizer;

final class SanitizerTest extends TestCase
{
	public function testSanitizerImplementsContract(): void
	{
		$this->assertInstanceOf(Contract\Sanitizer::class, new Sanitizer());
	}

	public function testSanitizesHtmlWithDefaults(): void
	{
		$this->assertSame(
			'<b>Boiler</b>',
			new Sanitizer()->sanitize('<script></script><b>Boiler</b>'),
		);
	}

	public function testCanUseHtmlStrategyExplicitly(): void
	{
		$this->assertSame(
			'<b>Boiler</b>',
			new Sanitizer()->sanitize('<script></script><b>Boiler</b>', Sanitizer::HTML),
		);
	}

	public function testCanRegisterAdditionalStrategy(): void
	{
		$sanitizer = new Sanitizer();
		$sanitizer->register('text', new class implements Contract\SanitizeStrategy {
			public function apply(string $value): string
			{
				return strip_tags($value);
			}
		});

		$this->assertSame('Boiler', $sanitizer->sanitize('<b>Boiler</b>', 'text'));
	}

	public function testConstructorStrategiesCanSetCustomDefault(): void
	{
		$sanitizer = new Sanitizer(
			defaultStrategy: 'text',
			strategies: [
				'text' => new class implements Contract\SanitizeStrategy {
					public function apply(string $value): string
					{
						return strip_tags($value);
					}
				},
			],
		);

		$this->assertSame('Boiler', $sanitizer->sanitize('<b>Boiler</b>'));
	}

	public function testConstructorStrategiesCanOverrideBuiltins(): void
	{
		$sanitizer = new Sanitizer(strategies: [
			Sanitizer::HTML => new class implements Contract\SanitizeStrategy {
				public function apply(string $value): string
				{
					return strip_tags($value);
				}
			},
		]);

		$this->assertSame('Boiler', $sanitizer->sanitize('<script></script><b>Boiler</b>'));
	}

	public function testRejectsUnknownStrategy(): void
	{
		$this->throws(UnexpectedValueException::class, 'Unknown sanitizer strategy `xml`');

		new Sanitizer()->sanitize('<b>Boiler</b>', 'xml');
	}

	public function testRejectsUnknownDefaultStrategy(): void
	{
		$this->throws(UnexpectedValueException::class, 'Unknown sanitizer strategy `xml`');

		new Sanitizer('xml');
	}
}
