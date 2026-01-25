<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Contract\Engine;
use Override;

final class Layout extends BaseTemplate
{
	/**
	 * @psalm-param non-empty-string $path
	 */
	public function __construct(
		string $path,
		protected readonly string $body,
		Sections $sections,
		?Engine $engine = null,
	) {
		parent::__construct($path, $sections, $engine);
	}

	/**
	 * Used in the layout template to get the content of the wrapped template.
	 */
	public function body(): string
	{
		return $this->body;
	}

	#[Override]
	protected function templateContext(array $context, array $whitelist, bool $autoescape): Context
	{
		return new LayoutContext($this, $context, $whitelist, $autoescape);
	}
}
