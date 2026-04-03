<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Exception\UnexpectedValueException;

final class StringProxyTest extends TestCase
{
	public function testProxyUnwrap(): void
	{
		$this->assertSame('<b>boiler</b>', $this->stringProxy('<b>boiler</b>')->unwrap());
	}

	public function testStringValue(): void
	{
		$html = '<b onclick="func()">boiler</b>';
		$value = $this->stringProxy($html);

		$this->assertSame('&lt;b onclick=&quot;func()&quot;&gt;boiler&lt;/b&gt;', (string) $value);
	}

	public function testStripFilter(): void
	{
		$proxy = $this->stringProxy('<b>boiler<br>plate</b>');

		$this->assertSame('boilerplate', (string) $proxy->strip());
	}

	public function testStripFilterWithAllowedTags(): void
	{
		$proxy = $this->stringProxy('<b>boiler<br>plate</b>');

		$this->assertSame('boiler&lt;br&gt;plate', (string) $proxy->strip('<br>'));
	}

	public function testStripReturnsStringProxy(): void
	{
		$proxy = $this->stringProxy('<b>boiler</b>');

		$this->assertInstanceOf($proxy::class, $proxy->strip());
	}

	public function testSanitizeFilter(): void
	{
		$proxy = $this->stringProxy('<script></script><b>boiler</b>');

		$this->assertSame('<b>boiler</b>', (string) $proxy->sanitize());
	}

	public function testSanitizeIsSafe(): void
	{
		$proxy = $this->stringProxy('<b>boiler</b>');
		$sanitized = $proxy->sanitize();

		// safe filter output is not auto-escaped
		$this->assertSame('<b>boiler</b>', (string) $sanitized);
	}

	public function testStripIsNotSafe(): void
	{
		$proxy = $this->stringProxy('<b>boiler</b>');
		$stripped = $proxy->strip();

		// strip is not safe, so output is auto-escaped
		$this->assertSame('boiler', (string) $stripped);
	}

	public function testChainedFilters(): void
	{
		$proxy = $this->stringProxy('<script></script><b>boiler</b>');

		// sanitize (safe) then strip (unsafe) — once safe, stays safe
		$result = $proxy->sanitize()->strip();
		$this->assertSame('boiler', (string) $result);
	}

	public function testSafeFlagPropagatesInChain(): void
	{
		$proxy = $this->stringProxy('<b>boiler</b>');

		// sanitize marks as safe, subsequent strip keeps it safe
		$result = $proxy->sanitize()->strip();
		$this->assertSame('boiler', (string) $result);
	}

	public function testUnsafeChainStaysUnsafe(): void
	{
		$proxy = $this->stringProxy('<b>boiler</b>');

		// strip is unsafe, chain remains unsafe, output gets escaped
		$stripped = $proxy->strip();
		$this->assertSame('boiler', (string) $stripped);
	}

	public function testUnknownFilterThrows(): void
	{
		$this->throws(UnexpectedValueException::class, 'Unknown filter `nope`');

		$this->stringProxy('test')->nope();
	}

	public function testFilterUnwrapReturnsFilteredValue(): void
	{
		$proxy = $this->stringProxy('<script></script><b>boiler</b>');
		$sanitized = $proxy->sanitize();

		$this->assertSame('<b>boiler</b>', $sanitized->unwrap());
	}
}
