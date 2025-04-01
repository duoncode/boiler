<?php

declare(strict_types=1);

namespace Duon\Boiler;

class LayoutValue
{
	/**
	 * @psalm-param non-empty-string $layout
	 */
	public function __construct(
		public readonly string $layout,
		public readonly ?array $context = null,
	) {}
}