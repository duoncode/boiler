<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Proxy\ArrayProxy;
use Duon\Boiler\Proxy\IteratorProxy;
use Duon\Boiler\Proxy\ObjectProxy;
use Duon\Boiler\Proxy\StringProxy;
use Duon\Boiler\Wrapper;
use Traversable;

final class WrapperTest extends TestCase
{
	public function testWrapNumber(): void
	{
		$this->assertSame(13, Wrapper::wrap(13));
		$this->assertSame(1.13, Wrapper::wrap(1.13));
	}

	public function testWrapString(): void
	{
		$this->assertInstanceOf(StringProxy::class, Wrapper::wrap('string'));
	}

	public function testWrapArray(): void
	{
		$warray = Wrapper::wrap([1, 2, 3]);

		$this->assertInstanceOf(ArrayProxy::class, $warray);
		$this->assertSame(false, is_array($warray));
		$this->assertSame(true, is_array($warray->unwrap()));
		$this->assertSame(3, count($warray));
	}

	public function testWrapIterator(): void
	{
		$iterator = (static function () {
			yield 1;
		})();
		$witerator = Wrapper::wrap($iterator);

		$this->assertInstanceOf(IteratorProxy::class, $witerator);
		$this->assertInstanceOf(Traversable::class, $witerator->unwrap());
		$this->assertSame(true, is_iterable($witerator->unwrap()));
	}

	public function testWrapObject(): void
	{
		$obj = new class {};

		$this->assertInstanceOf(ObjectProxy::class, Wrapper::wrap($obj));
	}

	public function testWrapStringable(): void
	{
		$obj = new class {
			public function __toString(): string
			{
				return '';
			}
		};

		$this->assertInstanceOf(ObjectProxy::class, Wrapper::wrap($obj));
	}

	public function testWrapResourceThrows(): void
	{
		$this->throws(UnexpectedValueException::class, 'resource');

		$resource = tmpfile();
		assert(is_resource($resource), 'tmpfile() must return a valid resource for this test');

		try {
			Wrapper::wrap($resource);
		} finally {
			fclose($resource);
		}
	}

	public function testNestingWrapping(): void
	{
		$value = new StringProxy('string');

		$this->assertInstanceOf(StringProxy::class, Wrapper::wrap($value));
		$this->assertSame('string', Wrapper::wrap($value)->unwrap());
		$this->assertSame(true, is_string(Wrapper::wrap($value)->unwrap()));
		$this->assertInstanceOf(StringProxy::class, Wrapper::wrap($value));
	}
}
