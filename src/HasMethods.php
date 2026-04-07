<?php

declare(strict_types=1);

namespace Duon\Boiler;

/** @internal */
interface HasMethods
{
	public function methods(): CustomMethods;
}
