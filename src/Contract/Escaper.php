<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Escaper
{
	public function escape(
		string $value,
		int $flags = ENT_QUOTES | ENT_SUBSTITUTE,
		string $encoding = 'UTF-8',
	): string;
}
