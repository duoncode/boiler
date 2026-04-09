<?php

declare(strict_types=1);

namespace Duon\Boiler;

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
	private ?Contract\Escapers $escapers;
	private ?Contract\Filters $filters;

	public function __construct(
		?Contract\Escapers $escapers = null,
		?Contract\Filters $filters = null,
	) {
		$this->escapers = $escapers;
		$this->filters = $filters;
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
		?string $escaper = null,
	): string {
		if ($value instanceof Proxy) {
			/** @psalm-suppress MixedAssignment unwrap returns mixed by design */
			$value = $value->unwrap();
		}

		if (is_string($value)) {
			return $this->escapeString($value, $escaper);
		}

		if ($value instanceof Stringable) {
			return $this->escapeString((string) $value, $escaper);
		}

		throw new RuntimeException('Value cannot be escaped as string');
	}

	#[Override]
	public function filter(string $name): Contract\Filter
	{
		return $this->filters()->get($name);
	}

	private function escapeString(string $value, ?string $escaper = null): string
	{
		$escapers = $this->escapers();

		return $escapers->get($escaper ?? $escapers->default)->escape($value);
	}

	private function escapers(): Contract\Escapers
	{
		return $this->escapers ??= new Escapers();
	}

	private function filters(): Contract\Filters
	{
		return $this->filters ??= new Filters();
	}
}
