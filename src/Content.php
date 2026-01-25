<?php

declare(strict_types=1);

namespace Duon\Boiler;

final class Content
{
	public function __construct(
		public readonly string $content,
		public readonly Context $templateContext,
	) {}
}
