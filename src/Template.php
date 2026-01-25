<?php

declare(strict_types=1);

namespace Duon\Boiler;

final class Template extends BaseTemplate
{
	/** @psalm-param list<class-string> $whitelist */
	protected function templateContext(array $context, array $whitelist, bool $autoescape): Context
	{
		return new TemplateContext($this, $context, $whitelist, $autoescape);
	}
}
