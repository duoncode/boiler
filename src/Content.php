<?php

declare(strict_types=1);

namespace Celemas\Boiler;

/** @internal */
final readonly class Content
{
	public function __construct(
		public string $content,
		public Context $templateContext,
	) {}
}
