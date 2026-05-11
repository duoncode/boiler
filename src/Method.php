<?php

declare(strict_types=1);

namespace Celemas\Boiler;

use Closure;

/** @internal */
final readonly class Method
{
	public Closure $callable;

	public function __construct(
		callable $callable,
		public bool $safe = false,
	) {
		$this->callable = Closure::fromCallable($callable);
	}
}
