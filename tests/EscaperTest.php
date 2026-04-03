<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract;
use Duon\Boiler\Escaper;
use Duon\Boiler\Exception\UnexpectedValueException;

final class EscaperTest extends TestCase
{
	public function testEscapesHtmlWithDefaults(): void
	{
		$escaper = new Escaper();

		$this->assertSame(
			'&lt;b&gt;&quot;Boiler&quot; &amp; more&lt;/b&gt;',
			$escaper->escape('<b>"Boiler" & more</b>'),
		);
	}

	public function testEscaperImplementsContract(): void
	{
		$this->assertInstanceOf(Contract\Escaper::class, new Escaper());
	}

	public function testCanUseHtmlStrategyExplicitly(): void
	{
		$escaper = new Escaper();

		$this->assertSame(
			'&lt;b&gt;&quot;Boiler&quot; &amp; more&lt;/b&gt;',
			$escaper->escape('<b>"Boiler" & more</b>', Escaper::HTML),
		);
	}

	public function testCanRegisterAdditionalStrategy(): void
	{
		$escaper = new Escaper();
		$escaper->register('caps', new class implements Contract\EscapeStrategy {
			public function apply(string $value): string
			{
				return strtoupper(htmlspecialchars($value));
			}
		});

		$this->assertSame('&LT;B&GT;BOILER&LT;/B&GT;', $escaper->escape('<b>boiler</b>', 'caps'));
	}

	public function testConstructorStrategiesCanSetCustomDefault(): void
	{
		$escaper = new Escaper(
			defaultStrategy: 'caps',
			strategies: [
				'caps' => new class implements Contract\EscapeStrategy {
					public function apply(string $value): string
					{
						return strtoupper(htmlspecialchars($value));
					}
				},
			],
		);

		$this->assertSame('&LT;B&GT;BOILER&LT;/B&GT;', $escaper->escape('<b>boiler</b>'));
	}

	public function testConstructorStrategiesCanOverrideBuiltins(): void
	{
		$escaper = new Escaper(strategies: [
			Escaper::HTML => new class implements Contract\EscapeStrategy {
				public function apply(string $value): string
				{
					return strtoupper($value);
				}
			},
		]);

		$this->assertSame('<B>BOILER</B>', $escaper->escape('<b>boiler</b>'));
	}

	public function testRejectsUnknownStrategy(): void
	{
		$this->throws(UnexpectedValueException::class, 'Unknown escape strategy `xml`');

		new Escaper()->escape('<b>Boiler</b>', 'xml');
	}

	public function testRejectsUnknownDefaultStrategy(): void
	{
		$this->throws(UnexpectedValueException::class, 'Unknown escape strategy `xml`');

		new Escaper('xml');
	}
}
