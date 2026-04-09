<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Override;

/** @internal */
final class ConfiguredEscapers implements Contract\Escapers
{
	private readonly Contract\Escapers $base;
	/** @psalm-suppress PossiblyUnusedProperty Used through Contract\Escapers::$default. */
	public readonly string $default;

	/** @var array<non-empty-string, Contract\Escaper> */
	private array $registry = [];

	public function __construct(?Contract\Escapers $base = null)
	{
		$this->base = $base ?? new Escapers();
		$this->default = $this->base->default;
	}

	/** @psalm-param non-empty-string $name */
	public function register(string $name, Contract\Escaper $escaper): void
	{
		$this->registry[$name] = $escaper;
	}

	#[Override]
	public function get(string $name): Contract\Escaper
	{
		return $this->registry[$name] ?? $this->base->get($name);
	}
}
