<?php

declare(strict_types=1);

namespace FiveOrbs\Boiler\Tests;

use FiveOrbs\Boiler\Sanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class SanitizerTest extends TestCase
{
	public const MALFORMED = '
<header>Test</header>
<aside><div>Test</div></aside>
<iframe src="example.com"></iframe>
<nav><ul><li>Test</li></ul></nav>
<article>
	<script>console.log("hans");</script>
	<section>
		<h1 onclick="console.log("hans");">Test</h1>
	</section>
</article>
<footer>Test</footer>';

	public function testCleanWithConfig(): void
	{
		$clean = "
<header>Test</header>
<aside><div>Test</div></aside>

<nav><ul><li>Test</li></ul></nav>
<article>
\t
	<section>
		<h1>Test</h1>
	</section>
</article>
<footer>Test</footer>";

		$this->assertSame($clean, (new Sanitizer())->clean(self::MALFORMED));
	}

	public function testCleanWithBlockExtension(): void
	{
		$config = (new HtmlSanitizerConfig())
			->allowSafeElements()
			->blockElement('header')
			->blockElement('footer')
			->blockElement('section');
		$clean = "
Test
<aside><div>Test</div></aside>

<nav><ul><li>Test</li></ul></nav>
<article>
\t
\t
		<h1>Test</h1>
\t
</article>
Test";

		$this->assertSame($clean, (new Sanitizer($config))->clean(self::MALFORMED));
	}
}
