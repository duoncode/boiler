<?php

declare(strict_types=1);

namespace Duon\Boiler;

final class LayoutSpec
{
	/**
	 * @psalm-param non-empty-string $path
	 */
	public function __construct(
		public readonly string $path,
		public readonly ?array $context = null,
		public readonly ?Location $location = null,
	) {}
}
