<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\LookupException;
use Duon\Boiler\Exception\UnexpectedValueException;

/**
 * @psalm-api
 *
 * @psalm-type DirsInput = non-empty-string|list<non-empty-string>|array<non-empty-string, non-empty-string>
 * @psalm-type Dirs = list<non-empty-string>|array<non-empty-string, non-empty-string>
 */
class Engine
{
	use RegistersMethod;

	/** @psalm-var Dirs */
	protected readonly array $dirs;

	/**
	 * @psalm-param DirsInput $dirs
	 * @psalm-param list<class-string> $whitelist
	 */
	public function __construct(
		array|string $dirs,
		public readonly bool $autoescape,
		protected readonly array $defaults,
		protected readonly array $whitelist,
	) {
		$this->dirs = $this->prepareDirs($dirs);
		$this->customMethods = new CustomMethods();
	}

	/**
	 * @psalm-param DirsInput $dirs
	 * @psalm-param list<class-string> $whitelist
	 */
	public static function create(
		array|string $dirs,
		array $defaults = [],
		array $whitelist = [],
	): self {
		return new self($dirs, true, $defaults, $whitelist);
	}

	/**
	 * @psalm-param DirsInput $dirs
	 * @psalm-param list<class-string> $whitelist
	 */
	public static function unescaped(
		array|string $dirs,
		array $defaults = [],
		array $whitelist = [],
	): self {
		return new self($dirs, false, $defaults, $whitelist);
	}

	/** @psalm-param non-empty-string $path */
	public function template(string $path): Template
	{
		if (!preg_match('/^[\w\.\/:_-]+$/u', $path)) {
			throw new UnexpectedValueException('The template path is invalid or empty');
		}

		$template = new Template($this->getFile($path), new Sections(), $this);
		$template->setCustomMethods($this->customMethods);

		return $template;
	}

	/** @psalm-param non-empty-string $path */
	public function render(
		string $path,
		array $context = [],
	): string {
		return $this->renderTemplate($path, $context, $this->autoescape);
	}

	/** @psalm-param non-empty-string $path */
	public function renderEscaped(
		string $path,
		array $context = [],
	): string {
		return $this->renderTemplate($path, $context, true);
	}

	/** @psalm-param non-empty-string $path */
	public function renderUnescaped(
		string $path,
		array $context = [],
	): string {
		return $this->renderTemplate($path, $context, false);
	}

	/** @psalm-param non-empty-string $path */
	protected function renderTemplate(
		string $path,
		array $context,
		bool $autoescape,
	): string {
		$template = $this->template($path);

		return $autoescape ?
			$template->renderEscaped(array_merge($this->defaults, $context), $this->whitelist) :
			$template->renderUnescaped(array_merge($this->defaults, $context), $this->whitelist);
	}

	/**
	 * @psalm-param non-empty-string $path
	 *
	 * @psalm-return non-empty-string
	 */
	public function getFile(string $path): string
	{
		[$namespace, $file] = $this->getSegments($path);
		$templatePath = $this->getTemplatePath($namespace, $file);

		if (!$templatePath->isValid()) {
			throw new LookupException($templatePath->error());
		}

		return $templatePath->path();
	}

	/** @psalm-param non-empty-string $path */
	public function exists(string $path): bool
	{
		try {
			$this->getFile($path);

			return true;
		} catch (LookupException) {
			return false;
		}
	}

	/** @psalm-param non-empty-string $file */
	protected function getTemplatePath(string|null $namespace, string $file): TemplatePath
	{
		if (!is_Null($namespace)) {
			if (array_key_exists($namespace, $this->dirs)) {
				return new TemplatePath($this->dirs[$namespace], $file);
			}

			throw new LookupException("Template namespace `{$namespace}` does not exist");
		}

		assert(count($this->dirs) > 0);

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
	protected function prepareDirs(array|string $dirs): array
	{
		$preparePath = function (string $dir): string {
			$realpath = realpath($dir);

			if ($realpath === false) {
				throw new LookupException(
					'Template directory does not exist ' . $dir,
				);
			}

			assert(!empty($realpath));

			return $realpath;
		};

		if (is_string($dirs)) {
			return [$preparePath($dirs)];
		}

		return array_map(
			function ($dir) use ($preparePath) {
				return $preparePath($dir);
			},
			$dirs,
		);
	}

	/** @return list{null|non-empty-string, non-empty-string} */
	protected function getSegments(string $path): array
	{
		if (strpos($path, ':') === false) {
			$path = trim($path);
			assert(!empty($path));

			return [null, $path];
		}

		$segments = array_map(fn($seg) => trim($seg), explode(':', $path));

		if (count($segments) == 2) {
			if (($segments[0] ?? '') && ($segments[1] ?? '')) {
				/** @var list{non-empty-string, non-empty-string} */
				return [$segments[0], $segments[1]];
			}

			throw new LookupException(
				"Invalid template format: '{$path}'. " .
					"Use 'namespace:template/path or template/path'.",
			);
		}

		throw new LookupException(
			"Invalid template format: '{$path}'. " .
				"Use 'namespace:template/path or template/path'.",
		);
	}
}