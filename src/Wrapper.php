<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\MissingSanitizerException;
use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Proxy\ArrayProxy;
use Duon\Boiler\Proxy\IteratorProxy;
use Duon\Boiler\Proxy\ObjectProxy;
use Duon\Boiler\Proxy\Proxy;
use Duon\Boiler\Proxy\StringProxy;
use Override;
use Stringable;
use Traversable;

/** @api */
final class Wrapper implements Contract\Wrapper
{
	private const string HTML_SANITIZER_CLASS = 'Symfony\\Component\\HtmlSanitizer\\HtmlSanitizer';

	private readonly Contract\Escaper $escaper;
	private readonly ?Contract\Sanitizer $sanitizer;

	public function __construct(
		?Contract\Escaper $escaper = null,
		?Contract\Sanitizer $sanitizer = null,
	) {
		$this->escaper = $escaper ?? new Escaper();
		$this->sanitizer =
			$sanitizer
			?? (
				class_exists(self::HTML_SANITIZER_CLASS)
					? new Sanitizer()
					: null
			);
	}

	#[Override]
	public function wrap(mixed $value): mixed
	{
		if (is_string($value)) {
			return new StringProxy($value, $this);
		}

		if (is_array($value)) {
			return new ArrayProxy($value, $this);
		}

		if (
			$value === null
			|| is_int($value)
			|| is_float($value)
			|| is_bool($value)
			|| is_resource($value)
		) {
			return $value;
		}

		if ($value instanceof Proxy) {
			return $value;
		}

		if ($value instanceof Traversable) {
			return new IteratorProxy($value, $this);
		}

		if (is_object($value)) {
			return new ObjectProxy($value, $this);
		}

		throw new UnexpectedValueException('Unsupported template value type');
	}

	#[Override]
	public function unwrap(mixed $value): mixed
	{
		if ($value instanceof Proxy) {
			return $value->unwrap();
		}

		if (!is_array($value)) {
			return $value;
		}

		return array_map($this->unwrap(...), $value);
	}

	#[Override]
	public function escape(
		mixed $value,
		?string $strategy = null,
	): string {
		if ($value instanceof StringProxy) {
			if ($strategy === null) {
				return (string) $value;
			}

			return $this->escaper->escape($value->unwrap(), $strategy);
		}

		if ($value instanceof Proxy) {
			/** @psalm-suppress MixedAssignment unwrap returns mixed by design */
			$value = $value->unwrap();
		}

		if (is_string($value)) {
			return $this->escaper->escape($value, $strategy);
		}

		if ($value instanceof Stringable) {
			return $this->escaper->escape((string) $value, $strategy);
		}

		throw new RuntimeException('Value cannot be escaped as string');
	}

	#[Override]
	public function sanitize(
		mixed $value,
		?string $strategy = null,
	): string {
		if ($value instanceof Proxy) {
			/** @psalm-suppress MixedAssignment unwrap returns mixed by design */
			$value = $value->unwrap();
		}

		if (is_string($value)) {
			return $this->sanitizer()->sanitize($value, $strategy);
		}

		if ($value instanceof Stringable) {
			return $this->sanitizer()->sanitize((string) $value, $strategy);
		}

		throw new RuntimeException('Value cannot be sanitized as string');
	}

	private function sanitizer(): Contract\Sanitizer
	{
		return $this->sanitizer ?? throw new MissingSanitizerException('No sanitizer configured');
	}
}
