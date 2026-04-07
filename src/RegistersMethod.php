<?php

declare(strict_types=1);

namespace Duon\Boiler;

trait RegistersMethod
{
	protected CustomMethods $customMethods;

	/** @psalm-param non-empty-string $name */
	public function method(string $name, callable $callable): static
	{
		$this->customMethods->add($name, $callable);

		return $this;
	}

	public function getMethods(): CustomMethods
	{
		return $this->customMethods;
	}
}
