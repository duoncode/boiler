<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract;
use Duon\Boiler\Contract\Escaper;
use Duon\Boiler\Contract\Filter;
use Duon\Boiler\Environment;
use Duon\Boiler\Escapers;
use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Filters;

final class EnvironmentTest extends TestCase
{
	public function testSetWrapperReturnsConfiguredWrapper(): void
	{
		$environment = new Environment();
		$wrapper = self::passthroughWrapper();

		$environment->setWrapper($wrapper);

		$this->assertSame($wrapper, $environment->wrapper());
	}

	public function testRegisterFilterWorksWithConfiguredReadOnlyFilters(): void
	{
		$environment = new Environment();
		$environment->setFilters(self::readOnlyFilters());
		$environment->registerFilter('upper', self::upperFilter());

		$this->assertSame('BOILER', $environment->wrapper()->filter('upper')->apply('boiler'));
	}

	public function testRegisterEscaperWorksWithConfiguredReadOnlyEscapers(): void
	{
		$environment = new Environment();
		$environment->setEscapers(self::readOnlyEscapers());
		$environment->registerEscaper('caps', self::capsEscaper());

		$this->assertSame('&LT;TAG&GT;', $environment->wrapper()->escape('<tag>', 'caps'));
	}

	public function testRegisterFilterRejectsInvalidName(): void
	{
		$this->throws(UnexpectedValueException::class, 'not a valid PHP method name');

		$environment = new Environment();
		$environment->registerFilter('no-dashes', self::upperFilter());
	}

	public function testRegisterEscaperRejectsEmptyName(): void
	{
		$this->throws(UnexpectedValueException::class, 'Escaper name must be a non-empty string');

		$environment = new Environment();
		$environment->registerEscaper('', self::capsEscaper());
	}

	public function testRegisterFilterRejectsConfiguredWrapper(): void
	{
		$this->throws(RuntimeException::class, 'Cannot register filter after wrapper is configured');

		$environment = new Environment();
		$environment->setWrapper($this->wrapper());
		$environment->registerFilter('upper', self::upperFilter());
	}

	public function testRegisterEscaperRejectsConfiguredWrapper(): void
	{
		$this->throws(RuntimeException::class, 'Cannot register escaper after wrapper is configured');

		$environment = new Environment();
		$environment->setWrapper($this->wrapper());
		$environment->registerEscaper('caps', self::capsEscaper());
	}

	public function testSetWrapperRejectsConfiguredFilters(): void
	{
		$this->throws(
			RuntimeException::class,
			'Cannot set wrapper after filters or escapers are configured',
		);

		$environment = new Environment();
		$environment->registerFilter('upper', self::upperFilter());
		$environment->setWrapper($this->wrapper());
	}

	public function testSetFiltersRejectsConfiguredWrapper(): void
	{
		$this->throws(RuntimeException::class, 'Cannot set filters after wrapper is configured');

		$environment = new Environment();
		$environment->setWrapper($this->wrapper());
		$environment->setFilters(new Filters());
	}

	public function testSetEscapersRejectsConfiguredWrapper(): void
	{
		$this->throws(RuntimeException::class, 'Cannot set escapers after wrapper is configured');

		$environment = new Environment();
		$environment->setWrapper($this->wrapper());
		$environment->setEscapers(new Escapers());
	}

	public function testSetWrapperRejectsSecondConfiguration(): void
	{
		$this->throws(RuntimeException::class, 'Wrapper is already configured');

		$environment = new Environment();
		$environment->setWrapper($this->wrapper());
		$environment->setWrapper($this->wrapper());
	}

	public function testSetFiltersRejectsSecondConfiguration(): void
	{
		$this->throws(RuntimeException::class, 'Filters are already configured');

		$environment = new Environment();
		$environment->setFilters(new Filters());
		$environment->setFilters(new Filters());
	}

	public function testSetEscapersRejectsSecondConfiguration(): void
	{
		$this->throws(RuntimeException::class, 'Escapers are already configured');

		$environment = new Environment();
		$environment->setEscapers(new Escapers());
		$environment->setEscapers(new Escapers());
	}

	public function testConfigurationIsSealedAfterWrapperIsMaterialized(): void
	{
		$this->throws(RuntimeException::class, 'Engine configuration is sealed');

		$environment = new Environment();
		$environment->wrapper();
		$environment->setEscapers(new Escapers());
	}

	private static function passthroughWrapper(): Contract\Wrapper
	{
		return new class implements Contract\Wrapper {
			public function wrap(mixed $value): mixed
			{
				return $value;
			}

			public function unwrap(mixed $value): mixed
			{
				return $value;
			}

			public function escape(mixed $value, ?string $escaper = null): string
			{
				return (string) $value;
			}

			public function filter(string $name): Filter
			{
				throw new UnexpectedValueException("Unknown filter `{$name}`");
			}
		};
	}

	private static function readOnlyFilters(): Contract\Filters
	{
		return new class implements Contract\Filters {
			public function get(string $name): Filter
			{
				throw new UnexpectedValueException("Unknown filter `{$name}`");
			}
		};
	}

	private static function readOnlyEscapers(): Contract\Escapers
	{
		return new class implements Contract\Escapers {
			public string $default {
				get => 'html';
			}

			public function get(string $name): Escaper
			{
				throw new UnexpectedValueException("Unknown escaper `{$name}`");
			}
		};
	}

	private static function upperFilter(): Filter
	{
		return new class implements Filter {
			public function apply(string $value, mixed ...$args): string
			{
				return strtoupper($value);
			}

			public function safe(): bool
			{
				return false;
			}
		};
	}

	private static function capsEscaper(): Escaper
	{
		return new class implements Escaper {
			public function escape(string $value): string
			{
				return strtoupper(htmlspecialchars($value));
			}
		};
	}
}
