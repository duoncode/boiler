<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

use Duon\Boiler\CustomMethods;

interface MethodRegister
{
	/** @psalm-param non-empty-string $name */
	public function registerMethod(string $name, callable $callable): void;

	public function getMethods(): CustomMethods;
}
