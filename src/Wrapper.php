<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Proxy\ArrayProxy;
use Duon\Boiler\Proxy\IteratorProxy;
use Duon\Boiler\Proxy\ObjectProxy;
use Duon\Boiler\Proxy\ProxyInterface;
use Duon\Boiler\Proxy\StringProxy;
use Traversable;

final class Wrapper
{
	public static function wrap(mixed $value): mixed
	{
		if (is_string($value)) {
			return new StringProxy($value);
		}

		if (is_array($value)) {
			return new ArrayProxy($value);
		}

		if (
			$value === null
			|| is_int($value)
			|| is_float($value)
			|| is_bool($value)
			|| is_resource($value)
		) {
			return $value;
		}

		if ($value instanceof ProxyInterface) {
			return $value;
		}

		if ($value instanceof Traversable) {
			return new IteratorProxy($value);
		}

		if (is_object($value)) {
			return new ObjectProxy($value);
		}

		throw new UnexpectedValueException('Unsupported template value type');
	}
}
