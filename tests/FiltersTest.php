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

		$this->assertInstanceOf(Contract\Filter::class, $filters->get('strip'));
	}

	public function testHasBuiltinSanitizeFilter(): void
	{
		$filters = new Filters();

		$this->assertInstanceOf(Contract\Filter::class, $filters->get('sanitize'));
	}

	public function testStripRemovesTags(): void
	{
		$filters = new Filters();

		$this->assertSame('Boiler', $filters->get('strip')->apply('<b>Boiler</b>'));
	}

	public function testStripAllowsSpecificTags(): void
	{
		$filters = new Filters();

		$this->assertSame('<b>Boiler</b>', $filters->get('strip')->apply(
			'<b>Boiler</b><script></script>',
			'<b>',
		));
	}

	public function testStripIsNotSafe(): void
	{
		$filters = new Filters();

		$this->assertFalse($filters->get('strip')->safe());
	}

	public function testSanitizeRemovesScripts(): void
	{
		$filters = new Filters();

		$this->assertSame(
			'<b>Boiler</b>',
			$filters->get('sanitize')->apply('<script></script><b>Boiler</b>'),
		);
	}

	public function testSanitizeIsSafe(): void
	{
		$filters = new Filters();

		$this->assertTrue($filters->get('sanitize')->safe());
	}

	public function testFiltersImplementsContract(): void
	{
		$this->assertInstanceOf(Contract\Filters::class, new Filters());
		$this->assertInstanceOf(Contract\RegistersFilters::class, new Filters());
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

		$this->assertSame('BOILER', $filters->get('upper')->apply('boiler'));
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

		$this->assertSame('custom', $filters->get('strip')->apply('<b>Boiler</b>'));
		$this->assertTrue($filters->get('strip')->safe());
	}

	public function testRejectsUnknownFilter(): void
	{
		$this->throws(UnexpectedValueException::class, 'Unknown filter `nope`');

		new Filters()->get('nope');
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

		$this->assertSame('Hel', $filters->get('truncate')->apply('Hello World', 3));
	}
}
