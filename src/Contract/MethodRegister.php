<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

use Duon\Boiler\CustomMethods;

/** @api */
interface MethodRegister
{
	/** @psalm-param non-empty-string $name */
	public function method(string $name, callable $callable): static;

	public function getMethods(): CustomMethods;
}
