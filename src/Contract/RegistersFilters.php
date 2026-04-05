<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface RegistersFilters extends Filters
{
	/** @psalm-assert non-empty-string $name */
	public function register(string $name, Filter $filter): void;
}
