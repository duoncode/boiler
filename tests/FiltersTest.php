<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract;
use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Filters;

final class FiltersTest extends TestCase
{
	public function testHasBuiltinStripFilter(): void
	{
		$filters = new Filters();

		$this->assertTrue($filters->has('strip'));
	}

	public function testHasBuiltinSanitizeFilter(): void
	{
		$filters = new Filters();

		$this->assertTrue($filters->has('sanitize'));
	}

	public function testStripRemovesTags(): void
	{
		$filters = new Filters();

		$this->assertSame('Boiler', $filters->apply('strip', '<b>Boiler</b>'));
	}

	public function testStripAllowsSpecificTags(): void
	{
		$filters = new Filters();

		$this->assertSame('<b>Boiler</b>', $filters->apply(
			'strip',
			'<b>Boiler</b><script></script>',
			'<b>',
		));
	}

	public function testStripIsNotSafe(): void
	{
		$filters = new Filters();

		$this->assertFalse($filters->safe('strip'));
	}

	public function testSanitizeRemovesScripts(): void
	{
		$filters = new Filters();

		$this->assertSame('<b>Boiler</b>', $filters->apply('sanitize', '<script></script><b>Boiler</b>'));
	}

	public function testSanitizeIsSafe(): void
	{
		$filters = new Filters();

		$this->assertTrue($filters->safe('sanitize'));
	}

	public function testCanRegisterCustomFilter(): void
	{
		$filters = new Filters();
		$filters->register('upper', new class implements Contract\Filter {
			public function apply(string $value, mixed ...$args): string
			{
				return strtoupper($value);
			}

			public function safe(): bool
			{
				return false;
			}
		});

		$this->assertTrue($filters->has('upper'));
		$this->assertSame('BOILER', $filters->apply('upper', 'boiler'));
	}

	public function testCanOverrideBuiltinFilter(): void
	{
		$filters = new Filters([
			'strip' => new class implements Contract\Filter {
				public function apply(string $value, mixed ...$args): string
				{
					return 'custom';
				}

				public function safe(): bool
				{
					return true;
				}
			},
		]);

		$this->assertSame('custom', $filters->apply('strip', '<b>Boiler</b>'));
		$this->assertTrue($filters->safe('strip'));
	}

	public function testRejectsUnknownFilter(): void
	{
		$this->throws(UnexpectedValueException::class, 'Unknown filter `nope`');

		new Filters()->apply('nope', 'value');
	}

	public function testRejectsEmptyFilterName(): void
	{
		$this->throws(UnexpectedValueException::class, 'Filter name must be a non-empty string');

		$filters = new Filters();
		$filters->register('', new class implements Contract\Filter {
			public function apply(string $value, mixed ...$args): string
			{
				return $value;
			}

			public function safe(): bool
			{
				return false;
			}
		});
	}

	public function testHasReturnsFalseForUnknown(): void
	{
		$this->assertFalse(new Filters()->has('nope'));
	}

	public function testFilterReceivesVariadicArgs(): void
	{
		$filters = new Filters();
		$filters->register('truncate', new class implements Contract\Filter {
			public function apply(string $value, mixed ...$args): string
			{
				$length = (int) ($args[0] ?? 10);

				return mb_substr($value, 0, $length);
			}

			public function safe(): bool
			{
				return false;
			}
		});

		$this->assertSame('Hel', $filters->apply('truncate', 'Hello World', 3));
	}
}
