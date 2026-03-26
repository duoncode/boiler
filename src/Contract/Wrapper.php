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
		int $flags = ENT_QUOTES | ENT_SUBSTITUTE,
		string $encoding = 'UTF-8',
	): string;

	public function clean(mixed $value): string;
}
