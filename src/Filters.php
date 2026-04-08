<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Filter\Sanitize;
use Duon\Boiler\Filter\Strip;

/** @api */
final class Filters implements Contract\RegistersFilters
{
	/** @var array<non-empty-string, Contract\Filter> */
	private array $registry;

	/** @param array<non-empty-string, Contract\Filter> $filters */
	public function __construct(array $filters = [])
	{
		$this->registry = $this->normalize(array_replace($this->builtins(), $filters));
	}

	#[\Override]
	public function register(string $name, Contract\Filter $filter): void
	{
		self::assertName($name);
		$this->registry[$name] = $filter;
	}

	#[\Override]
	public function get(string $name): Contract\Filter
	{
		return $this->registry[$name] ?? throw new UnexpectedValueException("Unknown filter `{$name}`");
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
		if (!preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $name)) {
			throw new UnexpectedValueException(
				"Filter name `{$name}` is not a valid PHP method name",
			);
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
				throw new UnexpectedValueException('Filter name must be a string');
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
}
