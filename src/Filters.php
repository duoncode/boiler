<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Filter\Sanitize;
use Duon\Boiler\Filter\Strip;

/** @api */
final class Filters
{
	/** @var array<non-empty-string, Contract\Filter> */
	private array $registry;

	/** @param array<non-empty-string, Contract\Filter> $filters */
	public function __construct(array $filters = [])
	{
		$this->registry = $this->normalize(array_replace($this->builtins(), $filters));
	}

	public function apply(string $name, string $value, mixed ...$args): string
	{
		return $this->filter($name)->apply($value, ...$args);
	}

	public function safe(string $name): bool
	{
		return $this->filter($name)->safe();
	}

	public function has(string $name): bool
	{
		return isset($this->registry[$name]);
	}

	public function register(string $name, Contract\Filter $filter): void
	{
		self::assertName($name);
		$this->registry[$name] = $filter;
	}

	/** @return array<non-empty-string, Contract\Filter> */
	private function builtins(): array
	{
		$builtins = [
			'strip' => new Strip(),
		];

		if (class_exists(\Symfony\Component\HtmlSanitizer\HtmlSanitizer::class)) {
			$builtins['sanitize'] = new Sanitize();
		}

		return $builtins;
	}

	/** @psalm-assert non-empty-string $name */
	private static function assertName(string $name): void
	{
		if ($name === '') {
			throw new UnexpectedValueException('Filter name must be a non-empty string');
		}
	}

	/**
	 * @param array<array-key, Contract\Filter> $filters
	 * @return array<non-empty-string, Contract\Filter>
	 */
	private function normalize(array $filters): array
	{
		/** @var array<non-empty-string, Contract\Filter> $normalized */
		$normalized = [];

		foreach ($filters as $name => $filter) {
			if (!is_string($name)) {
				throw new UnexpectedValueException('Filter name must be a non-empty string');
			}

			self::assertName($name);

			if (!$filter instanceof Contract\Filter) {
				throw new UnexpectedValueException(
					"Filter `{$name}` must implement `Duon\\Boiler\\Contract\\Filter`",
				);
			}

			$normalized[$name] = $filter;
		}

		return $normalized;
	}

	public function filter(string $name): Contract\Filter
	{
		return $this->registry[$name] ?? throw new UnexpectedValueException("Unknown filter `{$name}`");
	}
}
