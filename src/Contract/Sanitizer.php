<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Sanitizer
{
	public function clean(string $html): string;
}
