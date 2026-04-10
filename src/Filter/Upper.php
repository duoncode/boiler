<?php

declare(strict_types=1);

namespace Duon\Boiler\Filter;

use Duon\Boiler\Contract;
use Override;

/** @api */
final class Upper implements Contract\Filter
{
	#[Override]
	public function apply(string $value, mixed ...$args): string
	{
		return mb_strtoupper($value);
	}

	#[Override]
	public function safe(): bool
	{
		return false;
	}
}
