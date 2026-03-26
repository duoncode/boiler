<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract\Sanitizer as SanitizerContract;
use Duon\Boiler\Proxy\ArrayProxy;
use Duon\Boiler\Proxy\IteratorProxy;
use Duon\Boiler\Proxy\ObjectProxy;
use Duon\Boiler\Proxy\StringProxy;
use Duon\Boiler\Wrapper;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Traversable;

/**
 * @internal
 *
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
	public const ROOT_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
	public const DEFAULT_DIR = self::ROOT_DIR . 'default';
	public const DS = DIRECTORY_SEPARATOR;

	public function fulltrim(string $text): string
	{
		return trim(
			preg_replace(
				'/> </',
				'><',
				preg_replace(
					'/\s+/',
					' ',
					preg_replace('/\n/', '', $text),
				),
			),
		);
	}

	public function templates(array $templates = []): array
	{
		return array_merge($templates, [self::DEFAULT_DIR]);
	}

	public function namespaced(array $templates = []): array
	{
		return array_merge($templates, [
			'namespace' => self::DEFAULT_DIR,
		]);
	}

	public function additional(): array
	{
		return [
			'additional' => self::ROOT_DIR . 'additional',
		];
	}

	public function obj(): object
	{
		return new class {
			public function name(): string
			{
				return 'boiler';
			}
		};
	}

	public function throws(string $exception, ?string $message = null): void
	{
		$this->expectException($exception);

		if ($message) {
			$this->expectExceptionMessageMatches("/{$message}/");
		}
	}

	protected function wrapper(?SanitizerContract $sanitizer = null): Wrapper
	{
		return new Wrapper(sanitizer: $sanitizer);
	}

	/** @param array<array-key, mixed> $value */
	protected function arrayProxy(array $value): ArrayProxy
	{
		return new ArrayProxy($value, $this->wrapper());
	}

	protected function iteratorProxy(Traversable $value): IteratorProxy
	{
		return new IteratorProxy($value, $this->wrapper());
	}

	protected function objectProxy(
		object $value,
		?SanitizerContract $sanitizer = null,
	): ObjectProxy {
		return new ObjectProxy($value, $this->wrapper($sanitizer));
	}

	protected function stringProxy(
		string $value,
		?SanitizerContract $sanitizer = null,
	): StringProxy {
		return new StringProxy($value, $this->wrapper($sanitizer));
	}
}
