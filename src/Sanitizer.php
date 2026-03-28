<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Override;

/** @api */
final class Sanitizer implements Contract\Sanitizer
{
	public const string HTML = 'html';
	private const string HTML_SANITIZER_CLASS = 'Symfony\\Component\\HtmlSanitizer\\HtmlSanitizer';
	private const string HTML_SANITIZER_CONFIG_CLASS = 'Symfony\\Component\\HtmlSanitizer\\HtmlSanitizerConfig';

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

	/**
	 * @psalm-suppress UndefinedClass optional dependency
	 * @psalm-suppress MixedAssignment optional dependency
	 */
	private function sanitizeHtml(string $value): string
	{
		if (!self::isAvailable()) {
			throw new RuntimeException('Built-in sanitizer requires symfony/html-sanitizer');
		}

		$configClass = self::HTML_SANITIZER_CONFIG_CLASS;
		$sanitizerClass = self::HTML_SANITIZER_CLASS;
		$config = new $configClass();
		$config = $config->allowSafeElements();
		$sanitizer = new $sanitizerClass($config);
		$result = $sanitizer->sanitize($value);
		assert(is_string($result), 'Built-in sanitizer must return a string');

		return $result;
	}

	private static function isAvailable(): bool
	{
		return (
			class_exists(self::HTML_SANITIZER_CLASS) && class_exists(self::HTML_SANITIZER_CONFIG_CLASS)
		);
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
