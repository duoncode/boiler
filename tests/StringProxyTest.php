<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Exception\MissingSanitizerException;

final class StringProxyTest extends TestCase
{
	public function testProxyUnwrap(): void
	{
		$this->assertSame('<b>boiler</b>', $this->stringProxy('<b>boiler</b>')->unwrap());
	}

	public function testProxyStrip(): void
	{
		$this->assertSame('boiler<br>plate', $this->stringProxy('<b>boiler<br>plate</b>')->strip('<br>'));
		$this->assertSame('boiler<br>plate', $this->stringProxy('<b>boiler<br>plate</b>')->strip(['br']));
		$this->assertSame(
			'boiler<br>plate',
			$this->stringProxy('<b>boiler<br>plate</b>')->strip(['<br>']),
		);
		$this->assertSame('boilerplate', $this->stringProxy('<b>boiler<br>plate</b>')->strip(null));
		$this->assertSame('boilerplate', $this->stringProxy('<b>boiler<br>plate</b>')->strip());
	}

	public function testProxyClean(): void
	{
		$this->assertSame(
			'<b>boiler</b>',
			$this->stringProxy('<b onclick="function()">boiler</b>', new FakeSanitizer())->clean(),
		);
	}

	public function testProxyCleanThrowsWithoutSanitizer(): void
	{
		$this->throws(MissingSanitizerException::class, 'No sanitizer configured');

		$this->stringProxy('<b>boiler</b>')->clean();
	}

	public function testStringValue(): void
	{
		$html = '<b onclick="func()">boiler</b>';
		$value = $this->stringProxy($html);

		$this->assertSame('&lt;b onclick=&quot;func()&quot;&gt;boiler&lt;/b&gt;', (string) $value);
	}
}
