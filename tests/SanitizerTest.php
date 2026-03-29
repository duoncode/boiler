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
