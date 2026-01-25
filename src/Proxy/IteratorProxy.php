<?php

declare(strict_types=1);

namespace Duon\Boiler\Proxy;

use Duon\Boiler\Wrapper;
use Iterator;
use IteratorIterator;
use Override;

/**
 * @psalm-api
 *
 * @template-covariant TKey
 * @template-covariant TValue
 *
 * @template TIterator as \Traversable<TKey, TValue>
 *
 * @template-extends IteratorIterator<TKey, TValue, TIterator>
 */
class IteratorProxy extends IteratorIterator implements ProxyInterface
{
	#[Override]
	public function current(): mixed
	{
		$value = parent::current();

		/** @psalm-suppress MixedReturnStatement see above */
		return Wrapper::wrap($value);
	}

	public function unwrap(): ?Iterator
	{
		return $this->getInnerIterator();
	}

	public function toArray(): ArrayProxy
	{
		$inner = $this->getInnerIterator();

		return new ArrayProxy($inner ? iterator_to_array($inner) : []);
	}
}
