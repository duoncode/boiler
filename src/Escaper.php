<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\UnexpectedValueException;
use Override;

/** @api */
final class Escaper implements Contract\Escaper
{
	public const string HTML = 'html';

	public function __construct(
		private readonly string $defaultStrategy = self::HTML,
	) {
		self::assertStrategy($this->defaultStrategy);
	}

	#[Override]
	public function escape(
		string $value,
		?string $strategy = null,
	): string {
		return match ($strategy ?? $this->defaultStrategy) {
			self::HTML => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
			default => throw self::unknownStrategy($strategy ?? $this->defaultStrategy),
		};
	}

	private static function assertStrategy(string $strategy): void
	{
		match ($strategy) {
			self::HTML => null,
			default => throw self::unknownStrategy($strategy),
		};
	}

	private static function unknownStrategy(string $strategy): UnexpectedValueException
	{
		return new UnexpectedValueException("Unknown escape strategy `{$strategy}`");
	}
}
