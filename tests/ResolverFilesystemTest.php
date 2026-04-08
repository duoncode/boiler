<?php

declare(strict_types=1);

namespace Duon\Boiler\Tests;

use Duon\Boiler\Exception\LookupException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\Resolver\Filesystem;

final class ResolverFilesystemTest extends TestCase
{
	public function testResolvesTemplateFromFirstMatchingDirectory(): void
	{
		$resolver = new Filesystem($this->templates($this->additional()));

		$this->assertStringEndsWith(
			'tests/templates/default/simple.php',
			$resolver->resolve('simple'),
		);
	}

	public function testResolvesNamespacedTemplate(): void
	{
		$resolver = new Filesystem($this->namespaced($this->additional()));

		$this->assertStringEndsWith(
			'tests/templates/default/sub/home.php',
			$resolver->resolve('namespace:sub/home'),
		);
	}

	public function testRejectsInvalidTemplatePathCharacters(): void
	{
		$this->throws(UnexpectedValueException::class, 'invalid or empty');

		$resolver = new Filesystem($this->templates());
		$resolver->resolve("\0");
	}

	public function testRejectsInvalidTemplateFormat(): void
	{
		$this->throws(LookupException::class, 'Invalid template format');

		$resolver = new Filesystem($this->templates());
		$resolver->resolve('default:sub:index');
	}

	public function testThrowsForUnknownNamespace(): void
	{
		$this->throws(LookupException::class, 'Template namespace');

		$resolver = new Filesystem($this->namespaced());
		$resolver->resolve('missing:sub/home');
	}

	public function testPreventsTemplateTraversalOutsideConfiguredRoot(): void
	{
		$this->throws(LookupException::class, 'outside');

		$resolver = new Filesystem($this->templates());
		$resolver->resolve('../unreachable');
	}
}
