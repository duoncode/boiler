<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\UnexpectedValueException;

final class Methods
{
	/** @psalm-var array<non-empty-string, callable> */
	private array $methods = [];

	/** @psalm-param non-empty-string $name */
	public function add(string $name, callable $callable): void
	{
		$this->methods[$name] = $callable;
	}

	public function get(string $name): callable
	{
		return array_key_exists($name, $this->methods)
			? $this->methods[$name]
			: throw new UnexpectedValueException("Method '{$name}' does not exist");
	}
}
