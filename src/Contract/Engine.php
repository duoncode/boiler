<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Engine extends MethodRegister
{
	public bool $autoescape { get; }

	/** @psalm-param non-empty-string $path */
	public function template(string $path): Template;

	/** @psalm-param non-empty-string $path */
	public function render(string $path, array $context = []): string;

	/**
	 * @psalm-param non-empty-string $path
	 * @psalm-return non-empty-string
	 */
	public function getFile(string $path): string;
}
