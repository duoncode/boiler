<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Wrapper
{
	public function wrap(mixed $value): mixed;

	public function unwrap(mixed $value): mixed;

	public function escape(
		mixed $value,
		?string $strategy = null,
	): string;

	public function filter(string $name): Filter;
}
