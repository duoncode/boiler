<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface FilterRegister
{
	/** @psalm-assert non-empty-string $name */
	public function registerFilter(string $name, Filter $filter): void;
}
