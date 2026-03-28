<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract;
use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Sanitizer;

final class SanitizerTest extends TestCase
{
	public function testSanitizerImplementsContract(): void
	{
		$this->assertInstanceOf(Contract\Sanitizer::class, new Sanitizer());
	}

	public function testSanitizesHtmlWithDefaultsWhenAvailable(): void
	{
		$sanitizer = new Sanitizer();

		if (!$this->builtinSanitizerAvailable()) {
			$this->throws(RuntimeException::class, 'Built-in sanitizer requires');

			$sanitizer->sanitize('<script></script><b>Boiler</b>');

			return;
		}

		$this->assertSame(
			'<b>Boiler</b>',
			$sanitizer->sanitize('<script></script><b>Boiler</b>'),
		);
	}

	public function testCanUseHtmlStrategyExplicitlyWhenAvailable(): void
	{
		$sanitizer = new Sanitizer();

		if (!$this->builtinSanitizerAvailable()) {
			$this->throws(RuntimeException::class, 'Built-in sanitizer requires');

			$sanitizer->sanitize('<script></script><b>Boiler</b>', Sanitizer::HTML);

			return;
		}

		$this->assertSame(
			'<b>Boiler</b>',
			$sanitizer->sanitize('<script></script><b>Boiler</b>', Sanitizer::HTML),
		);
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
