<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract\Sanitizer;
use Override;

/** @internal */
final class FakeSanitizer implements Sanitizer
{
	#[Override]
	public function sanitize(
		string $value,
		?string $strategy = null,
	): string {
		$clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $value);
		$clean = preg_replace('/\s+on\w+="[^"]*"/i', '', $clean ?? $value);

		return $clean ?? $value;
	}
}
