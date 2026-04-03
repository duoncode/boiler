<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Strategy\SanitizeHtml;
use Override;

/** @api */
final class Sanitizer implements Contract\Sanitizer, Contract\SanitizeStrategyRegistry
{
	public const string HTML = 'html';

	/** @var array<non-empty-string, Contract\SanitizeStrategy> */
	private array $registry;

	/** @param array<non-empty-string, Contract\SanitizeStrategy> $strategies */
	public function __construct(
		private readonly string $defaultStrategy = self::HTML,
		array $strategies = [],
	) {
		self::assertStrategyName($this->defaultStrategy);
		$this->registry = $this->normalizeStrategies(array_replace($this->builtins(), $strategies));
	}

	#[Override]
	public function sanitize(
		string $value,
		?string $strategy = null,
	): string {
		return $this->strategy($strategy ?? $this->defaultStrategy)->apply($value);
	}

	#[Override]
	public function register(
		string $name,
		Contract\SanitizeStrategy $strategy,
	): void {
		self::assertStrategyName($name);
		$this->registry[$name] = $strategy;
	}

	/** @return array<non-empty-string, Contract\SanitizeStrategy> */
	private function builtins(): array
	{
		return [
			self::HTML => new SanitizeHtml(),
		];
	}

	/** @psalm-assert non-empty-string $name */
	private static function assertStrategyName(string $name): void
	{
		if ($name === '') {
			throw new UnexpectedValueException('Sanitizer strategy name must be a non-empty string');
		}
	}

	/**
	 * @param array<array-key, Contract\SanitizeStrategy> $strategies
	 * @return array<non-empty-string, Contract\SanitizeStrategy>
	 */
	private function normalizeStrategies(array $strategies): array
	{
		/** @var array<non-empty-string, Contract\SanitizeStrategy> $normalized */
		$normalized = [];

		foreach ($strategies as $name => $strategy) {
			if (!is_string($name)) {
				throw new UnexpectedValueException('Sanitizer strategy name must be a non-empty string');
			}

			self::assertStrategyName($name);

			if (!$strategy instanceof Contract\SanitizeStrategy) {
				throw new UnexpectedValueException(
					"Sanitizer strategy `{$name}` must implement `Duon\\Boiler\\Contract\\SanitizeStrategy`",
				);
			}

			$normalized[$name] = $strategy;
		}

		if (!isset($normalized[$this->defaultStrategy])) {
			throw self::unknownStrategy($this->defaultStrategy);
		}

		return $normalized;
	}

	private function strategy(string $strategy): Contract\SanitizeStrategy
	{
		return $this->registry[$strategy] ?? throw self::unknownStrategy($strategy);
	}

	private static function unknownStrategy(string $strategy): UnexpectedValueException
	{
		return new UnexpectedValueException("Unknown sanitizer strategy `{$strategy}`");
	}
}
