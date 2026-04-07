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
		$this->customMethods()->add($name, $callable);

		return $this;
	}

	public function methods(): CustomMethods
	{
		return $this->customMethods();
	}

	/** @psalm-param list<class-string> $whitelist */
	#[Override]
	protected function templateContext(array $context, array $whitelist, bool $autoescape): Context
	{
		return new TemplateContext($this, $context, $whitelist, $autoescape);
	}
}
