<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Override;

/** @api */
class Template extends BaseTemplate
{
	/** @psalm-param non-empty-string $name */
	public function method(string $name, callable $callable): static
	{
		$this->methods()->add($name, $callable);

		return $this;
	}

	/** @psalm-param list<class-string> $whitelist */
	#[Override]
	protected function context(array $context, array $whitelist, bool $autoescape): Context
	{
		return new TemplateContext($this, $context, $whitelist, $autoescape);
	}
}
