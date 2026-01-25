<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

use Duon\Boiler\LayoutValue;
use Duon\Boiler\Sections;

/** @api */
interface Template extends MethodRegister
{
	public Engine $engine { get; }
	public Sections $sections { get; }

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	public function render(array $context = [], array $whitelist = []): string;

	/**
	 * Defines a layout template that will be wrapped around this instance.
	 */
	public function setLayout(LayoutValue $layout): void;

	/**
	 * Returns the layout definition if set.
	 */
	public function layout(): ?LayoutValue;
}
