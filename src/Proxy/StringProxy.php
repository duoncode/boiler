<?php

declare(strict_types=1);

namespace Duon\Boiler\Proxy;

use Duon\Boiler\Contract\Wrapper;
use Duon\Boiler\Exception\UnexpectedValueException;
use Override;

/**
 * @api
 *
 * @implements Proxy<string>
 */
final class StringProxy implements Proxy
{
	private ?string $escaped = null;
	private bool $safe = false;

	public function __construct(
		private readonly string $value,
		private readonly Wrapper $wrapper,
	) {}

	/**
	 * Dispatch filters as virtual methods: $title->sanitize(), $title->strip('<b>'), etc.
	 *
	 * @param array<array-key, mixed> $args
	 */
	public function __call(string $name, array $args): self
	{
		if (!$this->wrapper->hasFilter($name)) {
			throw new UnexpectedValueException("Unknown filter `{$name}`");
		}

		$filtered = $this->wrapper->applyFilter($name, $this->value, ...$args);
		$proxy = new self($filtered, $this->wrapper);
		$proxy->safe = $this->safe || $this->wrapper->isFilterSafe($name);

		return $proxy;
	}

	public function __toString(): string
	{
		if ($this->safe) {
			return $this->value;
		}

		return $this->escaped ??= $this->wrapper->escape($this->value);
	}

	#[Override]
	public function unwrap(): string
	{
		return $this->value;
	}
}
