<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Engine extends MethodRegister
{
	/** @psalm-param non-empty-string $path */
	public function template(string $path): Template;

	/** @psalm-param non-empty-string $path */
	public function render(string $path, array $context = []): string ;
}
