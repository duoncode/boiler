<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Override;

/** @internal */
final class ConfiguredFilters implements Contract\Filters
{
	private readonly Contract\Filters $base;

	/** @var array<non-empty-string, Contract\Filter> */
	private array $registry = [];

	public function __construct(?Contract\Filters $base = null)
	{
		$this->base = $base ?? new Filters();
	}

	/** @psalm-param non-empty-string $name */
	public function register(string $name, Contract\Filter $filter): void
	{
		$this->registry[$name] = $filter;
	}

	#[Override]
	public function get(string $name): Contract\Filter
	{
		return $this->registry[$name] ?? $this->base->get($name);
	}
}
