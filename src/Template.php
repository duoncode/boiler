<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Override;

/** @api */
final class Template extends BaseTemplate
{
	/** @psalm-param list<class-string> $whitelist */
	#[Override]
	protected function templateContext(array $context, array $whitelist, bool $autoescape): Context
	{
		return new TemplateContext($this, $context, $whitelist, $autoescape);
	}
}
