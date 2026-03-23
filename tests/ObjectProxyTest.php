<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Proxy\ObjectProxy;
use Duon\Boiler\Proxy\StringProxy;
use PHPUnit\Framework\Attributes\TestDox;
use ValueError;

final class ObjectProxyTest extends TestCase
{
	public function testProxyUnwrap(): void
	{
		$object = new class {};
		$value = new ObjectProxy($object);

		$this->assertSame($object, $value->unwrap());
	}

	public function testStringableValue(): void
	{
		$stringable = new class {
			public string $value = 'test';

			public function __toString(): string
			{
				return '<b>boiler</b>';
			}

			public function testMethod(): string
			{
				return $this->value . $this->value;
			}
		};
		$value = new ObjectProxy($stringable);

		$this->assertSame('&lt;b&gt;boiler&lt;/b&gt;', (string) $value);
		$this->assertSame($stringable, $value->unwrap());
		$this->assertInstanceOf(StringProxy::class, $value->value);
		$this->assertSame('test', (string) $value->value);
		$value->value = 'boiler';
		$this->assertSame('boiler', (string) $value->value);
		$this->assertInstanceOf(StringProxy::class, $value->testMethod());
		$this->assertSame('boilerboiler', (string) $value->testMethod());
	}

	public function testObjectValid(): void
	{
		$object = new class {
			public function __invoke(string $str): string
			{
				return '<i>' . $str . '</i>';
			}

			public function html(): string
			{
				return '<b>boiler</b><script></script>';
			}
		};
		$value = new ObjectProxy($object);

		$this->assertSame(
			'&lt;b&gt;boiler&lt;/b&gt;&lt;script&gt;&lt;/script&gt;',
			(string) $value->html(),
		);
		$this->assertSame('<b>boiler</b>', $value->html()->clean());
		$this->assertSame('&lt;i&gt;test&lt;/i&gt;', (string) $value('test'));
	}

	public function testNonStringableObjectThrowsOnStringCast(): void
	{
		$this->throws(RuntimeException::class, 'not stringable');

		$value = new ObjectProxy(new class {});
		(string) $value;
	}

	public function testObjectNotInvokable(): void
	{
		$this->throws(RuntimeException::class, 'No such method');

		$object = new class {};
		$value = new ObjectProxy($object);

		$value();
	}

	public function testClosureValue(): void
	{
		$closure = static fn(): string => '<b>boiler</b><script></script>';
		$value = new ObjectProxy($closure);

		$this->assertSame('&lt;b&gt;boiler&lt;/b&gt;&lt;script&gt;&lt;/script&gt;', (string) $value());
		$this->assertSame('<b>boiler</b>', $value()->clean());
	}

	#[TestDox('Getter throws I')]
	public function testGetterThrowsI(): void
	{
		$this->throws(RuntimeException::class, 'No such property');

		$value = new ObjectProxy(new class {
			public function __toString(): string
			{
				return 'test';
			}
		});
		$value->test;
	}

	#[TestDox('Getter throws II')]
	public function testGetterThrowsII(): void
	{
		$this->throws(RuntimeException::class, 'No such property');

		$obj = new class {};
		$value = new ObjectProxy($obj);
		$value->test;
	}

	#[TestDox('Setter throws I')]
	public function testSetterThrowsI(): void
	{
		$this->throws(RuntimeException::class, 'No such property');

		$value = new ObjectProxy(new class {
			public function __toString(): string
			{
				return 'test';
			}
		});
		$value->test = null;
	}

	#[TestDox('Setter throws II')]
	public function testSetterThrowsII(): void
	{
		$this->throws(RuntimeException::class, 'No such property');

		$obj = new class {
			public function __set(string $name, mixed $value): void
			{
				if ($name && $value === null) {
					throw new ValueError();
				}
			}
		};
		$value = new ObjectProxy($obj);
		$value->test = null;
	}

	public function testMethodCallThrows(): void
	{
		$this->throws(RuntimeException::class, 'No such method');

		$value = new ObjectProxy(new class {
			public function __toString(): string
			{
				return 'test';
			}
		});
		$value->test();
	}
}
