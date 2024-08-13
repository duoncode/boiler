<?php

declare(strict_types=1);

use VacantPlanet\Boiler\ArrayValue;
use VacantPlanet\Boiler\IteratorValue;
use VacantPlanet\Boiler\Value;
use VacantPlanet\Boiler\Wrapper;

test('Number', function () {
    expect(Wrapper::wrap(13))->toBe(13);
    expect(Wrapper::wrap(1.13))->toBe(1.13);
});


test('String', function () {
    expect(Wrapper::wrap('string'))->toBeInstanceOf(Value::class);
});


test('Array', function () {
    $warray = Wrapper::wrap([1, 2, 3]);

    expect($warray)->toBeInstanceOf(ArrayValue::class);
    expect(is_array($warray))->toBe(false);
    expect(is_array($warray->unwrap()))->toBe(true);
    expect(count($warray))->toBe(3);
});


test('Iterator', function () {
    $iterator = (function () {
        yield 1;
    })();
    $witerator = Wrapper::wrap($iterator);

    expect($witerator)->toBeInstanceOf(IteratorValue::class);
    expect($witerator->unwrap())->toBeInstanceOf(Traversable::class);
    expect(is_iterable($witerator->unwrap()))->toBe(true);
});


test('Object', function () {
    $obj = new class () {
    };

    expect(Wrapper::wrap($obj))->toBeInstanceOf(Value::class);
});


test('Stringable', function () {
    $obj = new class () {
        public function __toString(): string
        {
            return '';
        }
    };

    expect(Wrapper::wrap($obj))->toBeInstanceOf(Value::class);
});


test('Nesting', function () {
    $value = new Value('string');

    expect(Wrapper::wrap($value))->toBeInstanceOf(Value::class);
    expect(Wrapper::wrap($value)->unwrap())->toBe('string');
    expect(is_string(Wrapper::wrap($value)->unwrap()))->toBe(true);
    expect(Wrapper::wrap($value))->toBeInstanceOf(Value::class);
});
