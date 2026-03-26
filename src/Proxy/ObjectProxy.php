<?php

declare(strict_types=1);

namespace Duon\Boiler\Proxy;

use Duon\Boiler\Contract\Wrapper as WrapperContract;
use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Override;
use Stringable;
use Traversable;

/**
 * @api
 *
 * @implements Proxy<object>
 */
final class ObjectProxy implements Proxy
{
	private readonly WrapperContract $wrapper;

	public function __construct(
		private readonly object $value,
		WrapperContract $wrapper,
	) {
		$this->wrapper = $wrapper;

		if ($this->value instanceof Traversable) {
			throw new UnexpectedValueException('Traversable objects must be wrapped as iterator proxies');
		}
	}

	public function __toString(): string
	{
		if (!$this->value instanceof Stringable) {
			throw new RuntimeException('Wrapped object is not stringable');
		}

		return $this->wrapper->escape($this->value);
	}

	public function __get(string $name): mixed
	{
		if ($this->hasPublicProperty($name)) {
			return $this->wrapper->wrap($this->value->{$name});
		}

		throw new RuntimeException('No such property');
	}

	public function __set(string $name, mixed $value): void
	{
		if ($this->hasPublicProperty($name)) {
			$this->value->{$name} = $value;

			return;
		}

		throw new RuntimeException('No such property');
	}

	public function __call(string $name, array $args): mixed
	{
		if (is_callable([$this->value, $name])) {
			return $this->wrapper->wrap($this->value->{$name}(...$args));
		}

		throw new RuntimeException('No such method');
	}

	public function __invoke(mixed ...$args): mixed
	{
		if (is_callable($this->value)) {
			return $this->wrapper->wrap(($this->value)(...$args));
		}

		throw new RuntimeException('No such method');
	}

	#[Override]
	public function unwrap(): object
	{
		return $this->value;
	}

	private function hasPublicProperty(string $name): bool
	{
		return array_key_exists($name, get_object_vars($this->value));
	}
}
