<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

interface Engine
{
	/** @psalm-param non-empty-string $path */
	public function template(string $path): Template;

	/** @psalm-param non-empty-string $path */
	public function render(string $path, array $context = []): string ;
}
