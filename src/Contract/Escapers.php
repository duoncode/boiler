<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Escapers
{
	public string $default { get; }

	public function get(string $name): Escaper;

	/** @psalm-assert non-empty-string $name */
	public function register(string $name, Escaper $escaper): void;
}
