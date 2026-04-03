<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface SanitizeStrategyRegistry
{
	/** @psalm-assert non-empty-string $name */
	public function register(string $name, SanitizeStrategy $strategy): void;
}
