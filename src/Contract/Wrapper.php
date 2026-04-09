<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

use Stringable;

/** @api */
interface Wrapper
{
	public function wrap(mixed $value): mixed;

	public function unwrap(mixed $value): mixed;

	public function escape(
		string|Stringable $value,
		?string $escaper = null,
	): string;

	public function filter(string $name): Filter;
}
