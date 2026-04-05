<?php

declare(strict_types=1);

namespace Duon\Boiler\Strategy;

use Duon\Boiler\Contract;
use Override;

/** @api */
final class EscapeHtml implements Contract\Escaper
{
	#[Override]
	public function escape(string $value): string
	{
		return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}
