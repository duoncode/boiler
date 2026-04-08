<?php

declare(strict_types=1);

namespace Duon\Boiler;

/** @api */
final class LayoutContext extends Context
{
	protected Layout $layout;

	/** @psalm-param list<class-string> $trusted */
	public function __construct(
		Layout $template,
		array $context,
		array $trusted,
		bool $autoescape,
	) {
		parent::__construct($template, $context, $trusted, $autoescape);
		$this->layout = $template;
	}

	public function body(): string
	{
		return $this->layout->body();
	}
}
