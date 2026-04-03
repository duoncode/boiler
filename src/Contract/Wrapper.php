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

	public function applyFilter(string $name, string $value, mixed ...$args): string;

	public function isFilterSafe(string $name): bool;

	public function hasFilter(string $name): bool;

	public function registerFilter(string $name, Filter $filter): void;
}
