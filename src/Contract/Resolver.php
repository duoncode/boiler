<?php

declare(strict_types=1);

namespace Celemas\Boiler\Contract;

/** @api */
interface Resolver
{
	/** @return non-empty-string */
	public function resolve(string $path): string;
}
