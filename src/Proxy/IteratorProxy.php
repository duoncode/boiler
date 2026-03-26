<?php

declare(strict_types=1);

namespace Duon\Boiler\Proxy;

use Duon\Boiler\Contract\Wrapper as WrapperContract;
use Iterator;
use IteratorIterator;
use Override;
use Traversable;

/**
 * @api
 *
 * @template-covariant TKey
 * @template-covariant TValue
 *
 * @template TIterator as \Traversable<TKey, TValue>
 *
 * @template-extends IteratorIterator<TKey, TValue, TIterator>
 * @implements Proxy<Iterator<TKey, TValue>|null>
 */
final class IteratorProxy extends IteratorIterator implements Proxy
{
	private readonly WrapperContract $wrapper;

	/** @param TIterator $iterator */
	public function __construct(Traversable $iterator, WrapperContract $wrapper)
	{
		parent::__construct($iterator);
		$this->wrapper = $wrapper;
	}

	#[Override]
	public function current(): mixed
	{
		$value = parent::current();

		/** @psalm-suppress MixedReturnStatement see above */
		return $this->wrapper->wrap($value);
	}

	#[Override]
	public function unwrap(): ?Iterator
	{
		return $this->getInnerIterator();
	}

	public function toArray(): ArrayProxy
	{
		$inner = $this->getInnerIterator();

		return new ArrayProxy($inner ? iterator_to_array($inner) : [], $this->wrapper);
	}
}
