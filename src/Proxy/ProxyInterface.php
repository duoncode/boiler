<?php

declare(strict_types=1);

namespace Duon\Boiler\Proxy;

/** @template-covariant TValue */
interface ProxyInterface
{
	/** @return TValue */
	public function unwrap(): mixed;
}
