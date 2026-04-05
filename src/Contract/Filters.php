<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Filters
{
	public function get(string $name): Filter;
}
