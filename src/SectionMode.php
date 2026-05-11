<?php

declare(strict_types=1);

namespace Celemas\Boiler;

/** @internal */
enum SectionMode
{
	case Assign;
	case Append;
	case Prepend;
	case Closed;
}
