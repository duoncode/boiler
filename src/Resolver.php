<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\LookupException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Override;

/**
 * @psalm-type DirsInput = non-empty-string|list<non-empty-string>|array<non-empty-string, non-empty-string>
 * @psalm-type Dirs = list<non-empty-string>|array<non-empty-string, non-empty-string>
 */
final class Resolver implements Contract\Resolver
{
	/** @psalm-var Dirs */
	private readonly array $dirs;
	/** @psalm-var array<string, non-empty-string> */
	private array $pathCache = [];

	/** @psalm-param DirsInput $dirs */
	public function __construct(
		array|string $dirs,
	) {
		$this->dirs = $this->prepareDirs($dirs);
	}

	/** @psalm-return non-empty-string */
	#[Override]
	public function resolve(string $path): string
	{
		if (isset($this->pathCache[$path])) {
			return $this->pathCache[$path];
		}

		if (!preg_match('/^[\w\.\/:_-]+$/u', $path)) {
			throw new UnexpectedValueException('The template path is invalid or empty');
		}

		[$namespace, $file] = $this->segments($path);
		$templatePath = $this->templatePath($namespace, $file);

		if (!$templatePath->isValid()) {
			throw new LookupException($templatePath->error());
		}

		return $this->pathCache[$path] = $templatePath->path();
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

	/**
	 * @psalm-param DirsInput $dirs
	 *
	 * @psalm-return Dirs
	 */
	private function prepareDirs(array|string $dirs): array
	{
		$preparePath = static function (string $dir): string {
			$realpath = realpath($dir);

			if ($realpath === false) {
				throw new LookupException(
					'Template directory does not exist ' . $dir,
				);
			}

			assert($realpath !== '', 'Resolved template directory path must not be empty');

			return $realpath;
		};

		if (is_string($dirs)) {
			return [$preparePath($dirs)];
		}

		return array_map(
			static function ($dir) use ($preparePath) {
				return $preparePath($dir);
			},
			$dirs,
		);
	}
}
