<?php

declare(strict_types=1);

namespace Duon\Boiler\Contract;

/** @api */
interface Engine extends MethodRegister
{
	public bool $autoescape { get; }

	public function wrapper(): Wrapper;

	public function setWrapper(Wrapper $wrapper): static;

	public function setFilters(Filters $filters): static;

	public function setEscapers(Escapers $escapers): static;

	/** @psalm-assert non-empty-string $name */
	public function escape(string $name, Escaper $with): static;

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
