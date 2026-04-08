<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Override;

/** @api */
final class Template extends BaseTemplate
{
	/** @psalm-param non-empty-string $name */
	public function method(string $name, callable $callable): static
	{
		$this->methods()->add($name, $callable);

		return $this;
	}

	/** @psalm-param list<class-string> $trusted */
	#[Override]
	protected function context(array $context, array $trusted, bool $autoescape): Context
	{
		return new TemplateContext($this, $context, $trusted, $autoescape);
	}
}
