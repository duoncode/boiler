<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface SanitizeStrategy
{
	public function apply(string $value): string;
}
