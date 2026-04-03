<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface SanitizeStrategyRegistry
{
	public function register(string $name, SanitizeStrategy $strategy): void;
}
