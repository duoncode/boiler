<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Contract\Escaper;
use Duon\Boiler\HtmlEscaper;

final class HtmlEscaperTest extends TestCase
{
	public function testEscapesHtmlWithDefaults(): void
	{
		$escaper = new HtmlEscaper();

		$this->assertSame(
			'&lt;b&gt;&quot;Boiler&quot; &amp; more&lt;/b&gt;',
			$escaper->escape('<b>"Boiler" & more</b>'),
		);
	}

	public function testEscaperImplementsContract(): void
	{
		$this->assertInstanceOf(Escaper::class, new HtmlEscaper());
	}

	public function testCanOverrideFlagsAndEncoding(): void
	{
		$escaper = new HtmlEscaper();

		$this->assertSame(
			'"quoted" &amp; &lt;tag&gt;',
			$escaper->escape('"quoted" & <tag>', ENT_NOQUOTES),
		);
	}
}
