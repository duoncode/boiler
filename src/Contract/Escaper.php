<?php

declare(strict_types=1);

namespace Celemas\Boiler\Contract;

/** @api */
interface Escaper
{
	public function escape(string $value): string;
}
