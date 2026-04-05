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
final class Wrapper implements Contract\FilterRegister, Contract\Wrapper
{
	private readonly Contract\Escapers $escapers;
	private readonly Contract\Filters $filters;

	public function __construct(
		?Contract\Escapers $escapers = null,
		?Contract\Filters $filters = null,
	) {
		$this->escapers = $escapers ?? new Escapers();
		$this->filters = $filters ?? new Filters();
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
		if ($value instanceof StringProxy) {
			if ($escaper === null) {
				return (string) $value;
			}

			return $this->escapeString($value->unwrap(), $escaper);
		}

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

	private function escapeString(string $value, ?string $escaper = null): string
	{
		return $this->escapers->get($escaper ?? $this->escapers->default)->escape($value);
	}

	#[Override]
	public function filter(string $name): Contract\Filter
	{
		return $this->filters->get($name);
	}

	#[Override]
	public function registerFilter(string $name, Contract\Filter $filter): void
	{
		if (!$this->filters instanceof Contract\RegistersFilters) {
			throw new RuntimeException('Configured filters do not support registration');
		}

		$this->filters->register($name, $filter);
	}
}
