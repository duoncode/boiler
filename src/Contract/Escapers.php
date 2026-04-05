<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Escapers
{
	public function get(string $name): Escaper;

	public function has(string $name): bool;

	/** @psalm-assert non-empty-string $name */
	public function register(string $name, Escaper $escaper): void;
}
