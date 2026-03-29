<?php

declare(strict_types=1);

namespace Duon\Boiler\Proxy;

use Duon\Boiler\Contract\Wrapper as WrapperContract;
use Override;

/**
 * @api
 *
 * @implements Proxy<string>
 */
final class StringProxy implements Proxy
{
	private ?string $escaped = null;

	public function __construct(
		private readonly string $value,
		private readonly WrapperContract $wrapper,
	) {}

	public function __toString(): string
	{
		return $this->escaped ??= $this->wrapper->escape($this->value);
	}

	#[Override]
	public function unwrap(): string
	{
		return $this->value;
	}

	/**
	 * @param array<array-key, string>|null|string $allowed
	 */
	public function strip(array|string|null $allowed = null): string
	{
		return strip_tags($this->value, $allowed);
	}

	public function sanitize(?string $strategy = null): string
	{
		return $this->wrapper->sanitize($this->value, $strategy);
	}
}
