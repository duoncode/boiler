<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract;
use Duon\Boiler\Contract\Escaper;
use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Proxy\ArrayProxy;
use Duon\Boiler\Proxy\IteratorProxy;
use Duon\Boiler\Proxy\ObjectProxy;
use Duon\Boiler\Proxy\StringProxy;
use Duon\Boiler\Wrapper;
use Traversable;

final class WrapperTest extends TestCase
{
	public function testWrapperImplementsContract(): void
	{
		$this->assertInstanceOf(Contract\Wrapper::class, new Wrapper());
	}

	public function testWrapNumber(): void
	{
		$wrapper = new Wrapper();

		$this->assertSame(13, $wrapper->wrap(13));
		$this->assertSame(1.13, $wrapper->wrap(1.13));
	}

	public function testWrapString(): void
	{
		$this->assertInstanceOf(StringProxy::class, new Wrapper()->wrap('string'));
	}

	public function testWrapArray(): void
	{
		$warray = new Wrapper()->wrap([1, 2, 3]);

		$this->assertInstanceOf(ArrayProxy::class, $warray);
		$this->assertSame(false, is_array($warray));
		$this->assertSame(true, is_array($warray->unwrap()));
		$this->assertSame(3, count($warray));
	}

	public function testWrapIterator(): void
	{
		$wrapper = new Wrapper();
		$iterator = (static function () {
			yield 1;
		})();
		$witerator = $wrapper->wrap($iterator);

		$this->assertInstanceOf(IteratorProxy::class, $witerator);
		$this->assertInstanceOf(Traversable::class, $witerator->unwrap());
		$this->assertSame(true, is_iterable($witerator->unwrap()));
	}

	public function testWrapObject(): void
	{
		$obj = new class {};

		$this->assertInstanceOf(ObjectProxy::class, new Wrapper()->wrap($obj));
	}

	public function testWrapStringable(): void
	{
		$obj = new class {
			public function __toString(): string
			{
				return '';
			}
		};

		$this->assertInstanceOf(ObjectProxy::class, new Wrapper()->wrap($obj));
	}

	public function testWrapResourcePassthrough(): void
	{
		$wrapper = new Wrapper();
		$resource = tmpfile();
		assert(is_resource($resource), 'tmpfile() must return a valid resource for this test');

		try {
			$this->assertSame($resource, $wrapper->wrap($resource));
		} finally {
			fclose($resource);
		}
	}

	public function testWrapClosedResourceThrows(): void
	{
		$this->throws(UnexpectedValueException::class, 'Unsupported template value type');

		$wrapper = new Wrapper();
		$resource = tmpfile();
		assert(is_resource($resource), 'tmpfile() must return a valid resource for this test');
		fclose($resource);

		$wrapper->wrap($resource);
	}

	public function testNestingWrapping(): void
	{
		$wrapper = new Wrapper();
		$value = $this->stringProxy('string');

		$this->assertInstanceOf(StringProxy::class, $wrapper->wrap($value));
		$this->assertSame('string', $wrapper->wrap($value)->unwrap());
		$this->assertSame(true, is_string($wrapper->wrap($value)->unwrap()));
		$this->assertInstanceOf(StringProxy::class, $wrapper->wrap($value));
	}

	public function testWrapUsesCustomEscaper(): void
	{
		$wrapper = new Wrapper(new class implements Escaper {
			public function escape(
				string $value,
				?string $strategy = null,
			): string {
				return strtoupper(htmlspecialchars($value));
			}
		});

		$this->assertSame('&LT;B&GT;BOILER&LT;/B&GT;', (string) $wrapper->wrap('<b>boiler</b>'));
	}

	public function testSanitizeUsesConfiguredSanitizer(): void
	{
		$wrapper = new Wrapper(sanitizer: new FakeSanitizer());

		$this->assertSame('<b>boiler</b>', $wrapper->sanitize('<b>boiler</b><script></script>'));
	}

	public function testSanitizeUnwrapsProxyValues(): void
	{
		$wrapper = new Wrapper(sanitizer: new FakeSanitizer());

		$this->assertSame(
			'<b>boiler</b>',
			$wrapper->sanitize($this->stringProxy('<b>boiler</b><script></script>')),
		);
	}

	public function testSanitizeSupportsStringableValues(): void
	{
		$wrapper = new Wrapper(sanitizer: new FakeSanitizer());
		$value = new class {
			public function __toString(): string
			{
				return '<b>boiler</b><script></script>';
			}
		};

		$this->assertSame('<b>boiler</b>', $wrapper->sanitize($value));
	}

	public function testSanitizePassesStrategyToSanitizer(): void
	{
		$wrapper = new Wrapper(sanitizer: new class implements \Duon\Boiler\Contract\Sanitizer {
			public function sanitize(
				string $value,
				?string $strategy = null,
			): string {
				return $strategy === 'text'
					? strip_tags($value)
					: $value;
			}
		});

		$this->assertSame('boiler', $wrapper->sanitize('<b>boiler</b>', 'text'));
	}

	public function testSanitizeUsesBuiltinSanitizer(): void
	{
		$this->assertSame(
			'<b>boiler</b>',
			new Wrapper()->sanitize('<script></script><b>boiler</b>'),
		);
	}

	public function testSanitizeRejectsNonStringableValues(): void
	{
		$this->throws(RuntimeException::class, 'Value cannot be sanitized as string');

		new Wrapper(sanitizer: new FakeSanitizer())->sanitize(13);
	}
}
