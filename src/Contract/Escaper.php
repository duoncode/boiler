<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Escaper
{
	public function escape(
		string $value,
		?string $strategy = null,
	): string;
}
