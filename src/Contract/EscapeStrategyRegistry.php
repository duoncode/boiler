<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface EscapeStrategyRegistry
{
	public function register(string $name, EscapeStrategy $strategy): void;
}
