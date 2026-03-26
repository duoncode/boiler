<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Contract\Escaper;
use Override;

/** @api */
final class HtmlEscaper implements Escaper
{
	#[Override]
	public function escape(
		string $value,
		int $flags = ENT_QUOTES | ENT_SUBSTITUTE,
		string $encoding = 'UTF-8',
	): string {
		return htmlspecialchars($value, $flags, $encoding);
	}
}
