<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\UnexpectedValueException;
use Override;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/** @api */
final class Sanitizer implements Contract\Sanitizer
{
	public const string HTML = 'html';

	public function __construct(
		private readonly string $defaultStrategy = self::HTML,
	) {
		self::assertStrategy($this->defaultStrategy);
	}

	#[Override]
	public function sanitize(
		string $value,
		?string $strategy = null,
	): string {
		return match ($strategy ?? $this->defaultStrategy) {
			self::HTML => $this->sanitizeHtml($value),
			default => throw self::unknownStrategy($strategy ?? $this->defaultStrategy),
		};
	}

	private function sanitizeHtml(string $value): string
	{
		$config = new HtmlSanitizerConfig()->allowSafeElements();

		return new HtmlSanitizer($config)->sanitize($value);
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
		return new UnexpectedValueException("Unknown sanitizer strategy `{$strategy}`");
	}
}
