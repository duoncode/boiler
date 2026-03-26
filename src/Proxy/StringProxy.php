<?php

declare(strict_types=1);

namespace Duon\Boiler\Proxy;

use Duon\Boiler\Contract\Wrapper as WrapperContract;
use Duon\Boiler\Sanitizer;
use Duon\Boiler\Wrapper;
use Override;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * @api
 *
 * @implements Proxy<string>
 */
final class StringProxy implements Proxy
{
	private const int ESCAPE_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE;
	private const string ESCAPE_ENCODING = 'UTF-8';

	private ?string $escaped = null;
	private readonly WrapperContract $wrapper;

	public function __construct(
		private readonly string $value,
		?WrapperContract $wrapper = null,
	) {
		$this->wrapper = $wrapper ?? new Wrapper();
	}

	public function __toString(): string
	{
		return $this->escaped ??= $this->wrapper->escape(
			$this->value,
			self::ESCAPE_FLAGS,
			self::ESCAPE_ENCODING,
		);
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

	public function clean(
		?HtmlSanitizerConfig $config = null,
	): string {
		return $config === null
			? $this->wrapper->clean($this->value)
			: new Sanitizer($config)->clean($this->value);
	}
}
