<?php

declare(strict_types=1);

namespace Duon\Boiler\Proxy;

use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Wrapper;
use Stringable;
use Traversable;

/**
 * @api
 *
 * @implements ProxyInterface<object>
 */
final class ObjectProxy implements ProxyInterface
{
	private const int ESCAPE_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE;
	private const string ESCAPE_ENCODING = 'UTF-8';

	public function __construct(
		private readonly object $value,
	) {
		if ($this->value instanceof Traversable) {
			throw new UnexpectedValueException('Traversable objects must be wrapped as iterator proxies');
		}
	}

	public function __toString(): string
	{
		if (!$this->value instanceof Stringable) {
			throw new RuntimeException('Wrapped object is not stringable');
		}

		return htmlspecialchars(
			(string) $this->value,
			self::ESCAPE_FLAGS,
			self::ESCAPE_ENCODING,
		);
	}

	public function __get(string $name): mixed
	{
		if ($this->hasPublicProperty($name)) {
			return Wrapper::wrap($this->value->{$name});
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
			return Wrapper::wrap($this->value->{$name}(...$args));
		}

		throw new RuntimeException('No such method');
	}

	public function __invoke(mixed ...$args): mixed
	{
		if (is_callable($this->value)) {
			return Wrapper::wrap(($this->value)(...$args));
		}

		throw new RuntimeException('No such method');
	}

	public function unwrap(): object
	{
		return $this->value;
	}

	private function hasPublicProperty(string $name): bool
	{
		return array_key_exists($name, get_object_vars($this->value));
	}
}
