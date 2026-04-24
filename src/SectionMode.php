<?php

declare(strict_types=1);

namespace Duon\Boiler;

/** @internal */
enum SectionMode
{
	case Assign;
	case Append;
	case Prepend;
	case Closed;
}
