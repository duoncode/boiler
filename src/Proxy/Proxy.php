<?php

declare(strict_types=1);

namespace Celemas\Boiler\Proxy;

/** @template-covariant TValue */
interface Proxy
{
	/** @return TValue */
	public function unwrap(): mixed;
}
