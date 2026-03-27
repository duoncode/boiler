<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract;
use Duon\Boiler\Escaper;

final class EscaperTest extends TestCase
{
	public function testEscapesHtmlWithDefaults(): void
	{
		$escaper = new Escaper();

		$this->assertSame(
			'&lt;b&gt;&quot;Boiler&quot; &amp; more&lt;/b&gt;',
			$escaper->escape('<b>"Boiler" & more</b>'),
		);
	}

	public function testEscaperImplementsContract(): void
	{
		$this->assertInstanceOf(Contract\Escaper::class, new Escaper());
	}

	public function testCanOverrideFlagsAndEncoding(): void
	{
		$escaper = new Escaper();

		$this->assertSame(
			'"quoted" &amp; &lt;tag&gt;',
			$escaper->escape('"quoted" & <tag>', ENT_NOQUOTES),
		);
	}
}
