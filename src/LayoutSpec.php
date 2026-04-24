<?php

declare(strict_types=1);

namespace Duon\Boiler;

/** @internal */
final readonly class LayoutSpec
{
	/**
	 * @psalm-param non-empty-string $path
	 */
	public function __construct(
		public string $path,
		public Location $location,
		public array $context = [],
	) {}
}
