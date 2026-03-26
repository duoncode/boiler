<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Contract\Escaper;
use Duon\Boiler\Contract\Sanitizer as SanitizerContract;
use Duon\Boiler\Contract\Wrapper as WrapperContract;
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
final class Wrapper implements WrapperContract
{
	private const int ESCAPE_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE;
	private const string ESCAPE_ENCODING = 'UTF-8';

	private readonly Escaper $escaper;

	public function __construct(
		?Escaper $escaper = null,
		private readonly ?SanitizerContract $sanitizer = null,
	) {
		$this->escaper = $escaper ?? new HtmlEscaper();
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
		int $flags = self::ESCAPE_FLAGS,
		string $encoding = self::ESCAPE_ENCODING,
	): string {
		if ($value instanceof StringProxy) {
			if ($flags === self::ESCAPE_FLAGS && $encoding === self::ESCAPE_ENCODING) {
				return (string) $value;
			}

			return $this->escaper->escape($value->unwrap(), $flags, $encoding);
		}

		if ($value instanceof Proxy) {
			/** @psalm-suppress MixedAssignment unwrap returns mixed by design */
			$value = $value->unwrap();
		}

		if (is_string($value)) {
			return $this->escaper->escape($value, $flags, $encoding);
		}

		if ($value instanceof Stringable) {
			return $this->escaper->escape((string) $value, $flags, $encoding);
		}

		throw new RuntimeException('Value cannot be escaped as string');
	}

	#[Override]
	public function clean(mixed $value): string
	{
		if ($value instanceof Proxy) {
			/** @psalm-suppress MixedAssignment unwrap returns mixed by design */
			$value = $value->unwrap();
		}

		if (is_string($value)) {
			return $this->sanitizer()->clean($value);
		}

		if ($value instanceof Stringable) {
			return $this->sanitizer()->clean((string) $value);
		}

		throw new RuntimeException('Value cannot be sanitized as string');
	}

	private function sanitizer(): SanitizerContract
	{
		return $this->sanitizer ?? throw new MissingSanitizerException('No sanitizer configured');
	}
}
