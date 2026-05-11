<?php

declare(strict_types=1);

namespace Celemas\Boiler\Contract;

/** @api */
interface Filters
{
	public function get(string $name): Filter;
}
