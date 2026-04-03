<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Strategy\EscapeHtml;
use Override;

/** @api */
final class Escaper implements Contract\Escaper, Contract\EscapeStrategyRegistry
{
	public const string HTML = 'html';

	/** @var array<non-empty-string, Contract\EscapeStrategy> */
	private array $registry;

	/** @param array<non-empty-string, Contract\EscapeStrategy> $strategies */
	public function __construct(
		private readonly string $defaultStrategy = self::HTML,
		array $strategies = [],
	) {
		self::assertStrategyName($this->defaultStrategy);
		$this->registry = $this->normalizeStrategies(array_replace($this->builtins(), $strategies));
	}

	#[Override]
	public function escape(
		string $value,
		?string $strategy = null,
	): string {
		return $this->strategy($strategy ?? $this->defaultStrategy)->apply($value);
	}

	#[Override]
	public function register(
		string $name,
		Contract\EscapeStrategy $strategy,
	): void {
		self::assertStrategyName($name);
		$this->registry[$name] = $strategy;
	}

	/** @return array<non-empty-string, Contract\EscapeStrategy> */
	private function builtins(): array
	{
		return [
			self::HTML => new EscapeHtml(),
		];
	}

	/** @psalm-assert non-empty-string $name */
	private static function assertStrategyName(string $name): void
	{
		if ($name === '') {
			throw new UnexpectedValueException('Escape strategy name must be a non-empty string');
		}
	}

	/**
	 * @param array<array-key, Contract\EscapeStrategy> $strategies
	 * @return array<non-empty-string, Contract\EscapeStrategy>
	 */
	private function normalizeStrategies(array $strategies): array
	{
		/** @var array<non-empty-string, Contract\EscapeStrategy> $normalized */
		$normalized = [];

		foreach ($strategies as $name => $strategy) {
			if (!is_string($name)) {
				throw new UnexpectedValueException('Escape strategy name must be a non-empty string');
			}

			self::assertStrategyName($name);

			if (!$strategy instanceof Contract\EscapeStrategy) {
				throw new UnexpectedValueException(
					"Escape strategy `{$name}` must implement `Duon\\Boiler\\Contract\\EscapeStrategy`",
				);
			}

			$normalized[$name] = $strategy;
		}

		if (!isset($normalized[$this->defaultStrategy])) {
			throw self::unknownStrategy($this->defaultStrategy);
		}

		return $normalized;
	}

	private function strategy(string $name): Contract\EscapeStrategy
	{
		return $this->registry[$name] ?? throw self::unknownStrategy($name);
	}

	private static function unknownStrategy(string $name): UnexpectedValueException
	{
		return new UnexpectedValueException("Unknown escape strategy `{$name}`");
	}
}
