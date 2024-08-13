<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use Traversable;

class Wrapper
{
    public static function wrap(mixed $value): mixed
    {
        if (is_scalar($value) && !is_string($value)) {
            return $value;
        }
        if (is_string($value)) {
            return new Value($value);
        }
        if ($value instanceof ValueInterface) {
            // Don't wrap already wrapped values again
            return $value;
        }
        if (is_array($value)) {
            return new ArrayValue($value);
        }
        if ($value instanceof Traversable) {
            return new IteratorValue($value);
        }
        if (is_null($value)) {
            return null;
        }

        return new Value($value);
    }
}
