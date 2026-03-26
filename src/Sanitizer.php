<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Contract\Sanitizer as SanitizerContract;
use Override;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/** @api */
final class Sanitizer implements SanitizerContract
{
	private HtmlSanitizer $sanitizer;

	public function __construct(?HtmlSanitizerConfig $config = null)
	{
		$config = $config ?: new HtmlSanitizerConfig()
			// Allow "safe" elements and attributes. All scripts will be removed
			// as well as other dangerous behaviors like CSS injection
			->allowSafeElements();

		$this->sanitizer = new HtmlSanitizer($config);
	}

	#[Override]
	public function clean(string $html): string
	{
		return $this->sanitizer->sanitize($html);
	}
}
