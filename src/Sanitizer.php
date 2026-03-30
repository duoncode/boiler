<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Strategy\SanitizeHtml;
use Override;

/** @api */
final class Sanitizer implements Contract\Sanitizer
{
	public const string HTML = 'html';

	/** @var array<non-empty-string, Contract\SanitizeStrategy> */
	private array $registry;

	/**
	 * @param array<non-empty-string, Contract\SanitizeStrategy> $strategies
	 */
	public function __construct(
		private readonly string $defaultStrategy = self::HTML,
		array $strategies = [],
	) {
		self::assertStrategyId($this->defaultStrategy);
		$this->registry = array_replace($this->builtins(), self::normalizeStrategies($strategies));
		$this->strategy($this->defaultStrategy);
	}

	public function register(
		string $id,
		Contract\SanitizeStrategy $strategy,
	): void {
		self::assertStrategyId($id);

		if (isset($this->registry[$id])) {
			throw self::duplicateStrategy($id);
		}

		$this->registry[$id] = $strategy;
	}

	#[Override]
	public function sanitize(
		string $value,
		?string $strategy = null,
	): string {
		return $this->strategy($strategy ?? $this->defaultStrategy)->apply($value);
	}

	/** @return array<non-empty-string, Contract\SanitizeStrategy> */
	private function builtins(): array
	{
		return [
			self::HTML => new SanitizeHtml(),
		];
	}

	/** @psalm-assert non-empty-string $id */
	private static function assertStrategyId(string $id): void
	{
		if ($id === '') {
			throw new UnexpectedValueException('Sanitizer strategy id must be a non-empty string');
		}
	}

	/**
	 * @param array<array-key, Contract\SanitizeStrategy> $strategies
	 * @return array<non-empty-string, Contract\SanitizeStrategy>
	 */
	private static function normalizeStrategies(array $strategies): array
	{
		/** @var array<non-empty-string, Contract\SanitizeStrategy> $normalized */
		$normalized = [];

		foreach ($strategies as $id => $strategy) {
			if (!is_string($id)) {
				throw new UnexpectedValueException('Sanitizer strategy id must be a non-empty string');
			}

			self::assertStrategyId($id);

			if (!$strategy instanceof Contract\SanitizeStrategy) {
				throw new UnexpectedValueException(
					"Sanitizer strategy `{$id}` must implement `Duon\\Boiler\\Contract\\SanitizeStrategy`",
				);
			}

			$normalized[$id] = $strategy;
		}

		return $normalized;
	}

	private function strategy(string $strategy): Contract\SanitizeStrategy
	{
		self::assertStrategyId($strategy);

		return $this->registry[$strategy] ?? throw self::unknownStrategy($strategy);
	}

	private static function duplicateStrategy(string $strategy): UnexpectedValueException
	{
		return new UnexpectedValueException("Sanitizer strategy `{$strategy}` is already registered");
	}

	private static function unknownStrategy(string $strategy): UnexpectedValueException
	{
		return new UnexpectedValueException("Unknown sanitizer strategy `{$strategy}`");
	}
}
