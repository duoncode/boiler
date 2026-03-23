<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Proxy\StringProxy;

final class StringProxyTest extends TestCase
{
	public function testProxyUnwrap(): void
	{
		$this->assertSame('<b>boiler</b>', new StringProxy('<b>boiler</b>')->unwrap());
	}

	public function testProxyStrip(): void
	{
		$this->assertSame('boiler<br>plate', new StringProxy('<b>boiler<br>plate</b>')->strip('<br>'));
		$this->assertSame('boiler<br>plate', new StringProxy('<b>boiler<br>plate</b>')->strip(['br']));
		$this->assertSame('boiler<br>plate', new StringProxy('<b>boiler<br>plate</b>')->strip(['<br>']));
		$this->assertSame('boilerplate', new StringProxy('<b>boiler<br>plate</b>')->strip(null));
		$this->assertSame('boilerplate', new StringProxy('<b>boiler<br>plate</b>')->strip());
	}

	public function testProxyClean(): void
	{
		$this->assertSame('<b>boiler</b>', new StringProxy('<b onclick="function()">boiler</b>')->clean());
	}

	public function testStringValue(): void
	{
		$html = '<b onclick="func()">boiler</b>';
		$value = new StringProxy($html);

		$this->assertSame('&lt;b onclick=&quot;func()&quot;&gt;boiler&lt;/b&gt;', (string) $value);
	}
}
