<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Closure;

final class Method
{
	public readonly Closure $callable;

	public function __construct(
		callable $callable,
		public readonly bool $safe = false,
	) {
		$this->callable = Closure::fromCallable($callable);
	}
}
