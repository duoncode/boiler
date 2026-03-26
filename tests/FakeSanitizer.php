<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract\Sanitizer;
use Override;

/** @internal */
final class FakeSanitizer implements Sanitizer
{
	#[Override]
	public function clean(string $html): string
	{
		$clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
		$clean = preg_replace('/\s+on\w+="[^"]*"/i', '', $clean ?? $html);

		return $clean ?? $html;
	}
}
