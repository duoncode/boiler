<?php

declare(strict_types=1);

namespace Duon\Boiler\Strategy;

use Duon\Boiler\Contract;
use Override;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/** @api */
final class SanitizeHtml implements Contract\SanitizeStrategy
{
	/** @var HtmlSanitizer|null */
	private mixed $sanitizer = null;

	#[Override]
	public function apply(string $value): string
	{
		return $this->sanitizer()->sanitize($value);
	}

	/** @return HtmlSanitizer */
	private function sanitizer(): mixed
	{
		return $this->sanitizer ??= new HtmlSanitizer(
			new HtmlSanitizerConfig()->allowSafeElements(),
		);
	}
}
