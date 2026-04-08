<?php

declare(strict_types=1);

namespace Duon\Boiler\Resolver;

use Duon\Boiler\Contract\Resolver;
use Duon\Boiler\Exception\LookupException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Duon\Boiler\TemplatePath;

final class Filesystem implements Resolver
{
	/** @param list<non-empty-string>|array<non-empty-string, non-empty-string> $dirs */
	public function __construct(
		private readonly array $dirs,
	) {}

	/** @psalm-return non-empty-string */
	public function resolve(string $path): string
	{
		if (!preg_match('/^[\w\.\/:_-]+$/u', $path)) {
			throw new UnexpectedValueException('The template path is invalid or empty');
		}

		[$namespace, $file] = $this->segments($path);
		$templatePath = $this->templatePath($namespace, $file);

		if (!$templatePath->isValid()) {
			throw new LookupException($templatePath->error());
		}

		return $templatePath->path();
	}

	/** @return list{null|non-empty-string, non-empty-string} */
	private function segments(string $path): array
	{
		if (!str_contains($path, ':')) {
			$path = trim($path);
			assert($path !== '', 'Template path must not be empty after trimming');

			return [null, $path];
		}

		$segments = array_map(static fn($seg) => trim($seg), explode(':', $path));

		if (count($segments) === 2) {
			if (($segments[0] ?? '') && ($segments[1] ?? '')) {
				/** @var list{non-empty-string, non-empty-string} */
				return [$segments[0], $segments[1]];
			}

			throw new LookupException(
				"Invalid template format: '{$path}'. " . "Use 'namespace:template/path or template/path'.",
			);
		}

		throw new LookupException(
			"Invalid template format: '{$path}'. " . "Use 'namespace:template/path or template/path'.",
		);
	}

	/** @psalm-param non-empty-string $file */
	private function templatePath(?string $namespace, string $file): TemplatePath
	{
		if (!is_null($namespace)) {
			if (array_key_exists($namespace, $this->dirs)) {
				return new TemplatePath($this->dirs[$namespace], $file);
			}

			throw new LookupException("Template namespace `{$namespace}` does not exist");
		}

		assert(count($this->dirs) > 0, 'At least one template directory must be configured');

		foreach ($this->dirs as $dir) {
			$templatePath = new TemplatePath($dir, $file);

			if ($templatePath->isValid()) {
				return $templatePath;
			}
		}

		return $templatePath;
	}
}
